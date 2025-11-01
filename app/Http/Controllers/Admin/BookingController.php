<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Fare;
use App\Models\Trip;
use App\Models\Route;
use App\Models\Booking;
use App\Models\Terminal;
use App\Models\TripStop;
use App\Models\RouteStop;
use App\Models\Timetable;
use App\Events\SeatLocked;
use App\Events\SeatUnlocked;
use Illuminate\Http\Request;
use App\Events\SeatConfirmed;
use App\Models\TimetableStop;
use App\Enums\PaymentMethodEnum;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use App\Services\TripFactoryService;
use Illuminate\Support\Facades\Auth;
use App\Services\AvailabilityService;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService,
        protected TripFactoryService $tripFactory,
        protected AvailabilityService $availabilityService,
    ) {}

    public function consoleIndex(): View
    {
        return view('admin.bookings.console', [
            'paymentMethods' => PaymentMethodEnum::options(),
        ]);
    }

    public function index(): View
    {
        return view('admin.bookings.index');
    }

    public function getData(Request $request)
    {
        $query = Booking::query()
            ->with(['trip.route', 'fromStop.terminal', 'toStop.terminal', 'seats', 'passengers', 'user'])
            ->latest();

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by channel
        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        // Filter by booking number
        if ($request->filled('booking_number')) {
            $query->where('booking_number', 'like', '%' . $request->booking_number . '%');
        }

        return datatables()
            ->eloquent($query)
            ->addColumn('booking_number', function (Booking $booking) {
                return '<span class="badge bg-primary">#' . $booking->booking_number . '</span>';
            })
            ->addColumn('created_at', function (Booking $booking) {
                return $booking->created_at->format('d M Y, H:i');
            })
            ->addColumn('route', function (Booking $booking) {
                $from = $booking->fromStop?->terminal?->code ?? 'N/A';
                $to = $booking->toStop?->terminal?->code ?? 'N/A';

                return '<strong>' . $from . ' → ' . $to . '</strong>';
            })
            ->addColumn('seats', function (Booking $booking) {
                $seatNumbers = $booking->seats->pluck('seat_number')->join(', ');

                return '<span class="badge bg-info">' . $seatNumbers . '</span>';
            })
            ->addColumn('passengers_count', function (Booking $booking) {
                return '<span class="badge bg-secondary">' . $booking->passengers->count() . ' passengers</span>';
            })
            ->addColumn('amount', function (Booking $booking) {
                return '<strong>PKR ' . number_format($booking->final_amount, 2) . '</strong>';
            })
            ->addColumn('channel', function (Booking $booking) {
                $icons = [
                    'counter' => '<i class="fas fa-store"></i> Counter',
                    'phone' => '<i class="fas fa-phone"></i> Phone',
                    'online' => '<i class="fas fa-globe"></i> Online',
                ];

                return $icons[$booking->channel] ?? $booking->channel;
            })
            ->addColumn('status', function (Booking $booking) {
                $badgeClass = match ($booking->status) {
                    'confirmed' => 'bg-success',
                    'hold' => 'bg-warning',
                    'checked_in' => 'bg-info',
                    'boarded' => 'bg-primary',
                    'cancelled' => 'bg-danger',
                    default => 'bg-secondary',
                };

                return '<span class="badge ' . $badgeClass . '">' . ucfirst($booking->status) . '</span>';
            })
            ->addColumn('payment_status', function (Booking $booking) {
                $badgeClass = match ($booking->payment_status) {
                    'paid' => 'bg-success',
                    'unpaid' => 'bg-danger',
                    'partial' => 'bg-warning',
                    default => 'bg-secondary',
                };

                return '<span class="badge ' . $badgeClass . '">' . ucfirst($booking->payment_status) . '</span>';
            })
            ->addColumn('actions', function (Booking $booking) {
                return '
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary" onclick="viewBookingDetails(' . $booking->id . ')">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <a href="' . route('admin.bookings.edit', $booking->id) . '" class="btn btn-outline-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <button type="button" class="btn btn-outline-danger" onclick="deleteBooking(' . $booking->id . ')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['booking_number', 'route', 'seats', 'passengers_count', 'amount', 'channel', 'status', 'payment_status', 'actions'])
            ->make(true);
    }

    public function create(): View
    {
        return view('admin.bookings.create');
    }

    public function show(Booking $booking): View
    {
        $booking->load(['trip.route', 'fromStop.terminal', 'toStop.terminal', 'seats', 'passengers', 'user']);

        return view('admin.bookings.show', compact('booking'));
    }

    public function edit(Booking $booking): View
    {
        $booking->load(['trip.stops', 'seats', 'passengers', 'fromStop.terminal', 'toStop.terminal']);

        return view('admin.bookings.edit', [
            'booking' => $booking,
            'paymentMethods' => PaymentMethodEnum::options(),
        ]);
    }

    public function update(Request $request, Booking $booking)
    {
        // Check if departure time has passed - prevent status update if it has
        $departureTime = $booking->trip?->departure_datetime;
        $departurePassed = $departureTime && $departureTime->isPast();

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', array_merge(
                array_column(\App\Enums\BookingStatusEnum::cases(), 'value'),
                ['checked_in', 'boarded']
            )),
            'payment_status' => 'required|in:paid,unpaid,partial',
            'payment_method' => 'nullable|in:cash,card,mobile_wallet,bank_transfer',
            'online_transaction_id' => 'nullable|string|max:100',
            'amount_received' => 'nullable|numeric|min:0',
            'reserved_until' => 'nullable|date_format:Y-m-d\TH:i',
            'notes' => 'nullable|string|max:500',
            'passengers' => 'nullable|array',
            'passengers.*.name' => 'nullable|string|max:100',
            'passengers.*.gender' => 'nullable|in:male,female',
            'passengers.*.age' => 'nullable|integer|min:1|max:120',
            'passengers.*.cnic' => 'nullable|string|max:20',
            'passengers.*.phone' => 'nullable|string|max:20',
            'passengers.*.email' => 'nullable|email|max:100',
        ]);

        try {
            // If departure has passed, keep the original status
            $statusToUpdate = $departurePassed ? $booking->status : $validated['status'];

            // Validate transaction ID for non-cash payments
            $paymentMethod = $validated['payment_method'] ?? $booking->payment_method ?? 'cash';
            if ($paymentMethod !== 'cash' && empty($validated['online_transaction_id'])) {
                throw new \Exception('Transaction ID is required for non-cash payments');
            }

            // Calculate return amount for cash payments
            $amountReceived = $validated['amount_received'] ?? 0;
            $returnAmount = 0;
            if ($paymentMethod === 'cash' && $amountReceived > 0) {
                $returnAmount = max(0, $amountReceived - $booking->final_amount);
            }

            // Update basic booking information
            $updateData = [
                'status' => $statusToUpdate,
                'payment_status' => $validated['payment_status'],
                'payment_method' => $paymentMethod,
                'online_transaction_id' => $validated['online_transaction_id'] ?? ($paymentMethod === 'cash' ? null : $booking->online_transaction_id),
                'reserved_until' => $validated['status'] === 'hold' ? ($validated['reserved_until'] ?? now()->addMinutes(15)) : null,
                'notes' => $validated['notes'] ?? null,
            ];

            // Add payment received and return amount if cash payment
            if ($paymentMethod === 'cash') {
                $updateData['payment_received_from_customer'] = $amountReceived;
                $updateData['return_after_deduction_from_customer'] = $returnAmount;
            }

            $booking->update($updateData);

            // Update passengers if provided
            if ($validated['passengers'] ?? false) {
                $passengers = $booking->passengers()->get();

                foreach ($validated['passengers'] as $index => $passengerData) {
                    if (isset($passengers[$index])) {
                        $passengers[$index]->update([
                            'name' => $passengerData['name'] ?? $passengers[$index]->name,
                            'gender' => $passengerData['gender'] ?? $passengers[$index]->gender,
                            'age' => $passengerData['age'] ?? $passengers[$index]->age,
                            'cnic' => $passengerData['cnic'] ?? $passengers[$index]->cnic,
                            'phone' => $passengerData['phone'] ?? $passengers[$index]->phone,
                            'email' => $passengerData['email'] ?? $passengers[$index]->email,
                        ]);
                    }
                }
            }

            return response()->json([
                'message' => 'Booking updated successfully',
                'booking' => $booking->load(['trip.route', 'fromStop.terminal', 'toStop.terminal', 'seats', 'passengers']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update booking',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function destroy(Booking $booking)
    {
        // TODO: Implement destroy
        return response()->json(['message' => 'Booking deleted']);
    }

    public function getTripPassengers(int $tripId): JsonResponse
    {
        try {
            $bookings = Booking::query()
                ->where('trip_id', $tripId)
                ->where('status', '!=', 'cancelled')
                ->with(['passengers', 'seats', 'fromStop.terminal', 'toStop.terminal'])
                ->get();

            $passengers = [];

            foreach ($bookings as $booking) {
                // Get all seat numbers for this booking
                $seatNumbers = $booking->seats->pluck('seat_number')->toArray();
                $seatsDisplay = implode(', ', $seatNumbers);

                foreach ($booking->passengers as $passenger) {
                    $passengers[] = [
                        'id' => $passenger->id,
                        'booking_id' => $booking->id,
                        'name' => $passenger->name,
                        'gender' => $passenger->gender,
                        'age' => $passenger->age,
                        'cnic' => $passenger->cnic,
                        'phone' => $passenger->phone,
                        'email' => $passenger->email,
                        'seat_numbers' => $seatNumbers,
                        'seats_display' => $seatsDisplay,
                        'from_stop' => $booking->fromStop?->terminal?->name ?? 'N/A',
                        'from_code' => $booking->fromStop?->terminal?->code ?? 'N/A',
                        'to_stop' => $booking->toStop?->terminal?->name ?? 'N/A',
                        'to_code' => $booking->toStop?->terminal?->code ?? 'N/A',
                        'status' => $booking->status,
                        'payment_status' => $booking->payment_status,
                        'payment_method' => $booking->payment_method,
                        'booking_number' => $booking->booking_number,
                        'channel' => $booking->channel,
                    ];
                }
            }

            return response()->json($passengers);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function getBookingDetailsForConsole(int $bookingId): JsonResponse
    {
        try {
            $booking = Booking::query()
                ->where('id', $bookingId)
                ->with(['passengers', 'seats', 'fromStop.terminal', 'toStop.terminal', 'user'])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'booking' => [
                    'id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'status' => $booking->status,
                    'channel' => $booking->channel,
                    'payment_method' => $booking->payment_method,
                    'payment_status' => $booking->payment_status,
                    'total_fare' => $booking->total_fare,
                    'discount_amount' => $booking->discount_amount,
                    'tax_amount' => $booking->tax_amount,
                    'final_amount' => $booking->final_amount,
                    'notes' => $booking->notes,
                    'transaction_id' => $booking->online_transaction_id,
                    'created_at' => $booking->created_at,
                    'updated_at' => $booking->updated_at,
                    'from_stop' => $booking->fromStop?->terminal?->name,
                    'to_stop' => $booking->toStop?->terminal?->name,
                    'passengers' => $booking->passengers->map(fn($p) => [
                        'name' => $p->name,
                        'age' => $p->age,
                        'gender' => $p->gender,
                        'cnic' => $p->cnic,
                        'phone' => $p->phone,
                        'email' => $p->email,
                        'seat_number' => $booking->seats->where('id', '!=', null)->first()?->seat_number,
                    ])->toArray(),
                    'seats' => $booking->seats->map(fn($s) => [
                        'seat_number' => $s->seat_number,
                        'gender' => $s->gender,
                        'fare' => $s->fare,
                        'tax_amount' => $s->tax_amount,
                        'final_amount' => $s->final_amount,
                    ])->toArray(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function getTerminals(Request $request): JsonResponse
    {
        $user = Auth::user();
        $terminals = Terminal::query()
            ->where('status', 'active')
            ->when($user->terminal_id, function ($query) use ($user) {
                $query->where('id', $user->terminal_id);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'city_id', 'code']);

        return response()->json(['terminals' => $terminals]);
    }

    public function getRoutes(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'terminal_id' => 'required|exists:terminals,id',
        ]);

        $routes = Route::query()
            ->whereHas('routeStops', fn($q) => $q->where('terminal_id', $validated['terminal_id']))
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'direction', 'base_currency']);

        return response()->json(['routes' => $routes]);
    }

    public function getStops(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'route_id' => 'required|exists:routes,id',
        ]);

        $stops = RouteStop::query()
            ->where('route_id', $validated['route_id'])
            ->with('terminal:id,name,code')
            ->orderBy('sequence')
            ->get(['id', 'terminal_id', 'sequence', 'route_id']);

        return response()->json(['stops' => $stops]);
    }

    // public function getRouteStops(Request $request): JsonResponse
    // {
    //     $validated = $request->validate([
    //         'from_terminal_id' => 'required|exists:terminals,id',
    //     ]);

    //     try {
    //         // Find all routes that have this terminal, then get forward stops
    //         $routes = Route::query()
    //             ->whereHas('routeStops', fn($q) => $q->where('terminal_id', $validated['from_terminal_id']))
    //             ->where('status', 'active')
    //             ->get();

    //         $routeStops = [];
    //         foreach ($routes as $route) {
    //             // Get all stops for this route
    //             $stops = RouteStop::where('route_id', $route->id)
    //                 ->with('terminal:id,name,code')
    //                 ->orderBy('sequence')
    //                 ->get();

    //             // Find the from terminal sequence
    //             $fromTerminalSequence = $stops->firstWhere('terminal_id', $validated['from_terminal_id'])?->sequence;

    //             if ($fromTerminalSequence === null) {
    //                 continue;
    //             }

    //             // Get only forward stops (sequence > from_terminal sequence)
    //             foreach ($stops as $stop) {
    //                 if ($stop->sequence > $fromTerminalSequence) {
    //                     $routeStops[] = $stop;
    //                 }
    //             }
    //         }

    //         return response()->json(['route_stops' => $routeStops]);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 400);
    //     }
    // }
    public function getRouteStops(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_terminal_id' => 'required|exists:terminals,id',
        ]);

        try {
            $user = Auth::user();
            if ($user->routes()->exists()) {
                $routes = $user->routes->where('status', 'active');
            } else {
                $routes = Route::query()
                    ->whereHas('routeStops', fn($q) => $q->where('terminal_id', $validated['from_terminal_id']))
                    ->where('status', 'active')
                    ->get();
            }

            $routeStops = collect();

            foreach ($routes as $route) {

                $stops = RouteStop::where('route_id', $route->id)
                    ->with('terminal:id,name,code')
                    ->orderBy('sequence')
                    ->get();

                // Remove user's own stop
                $filteredStops = $stops->filter(
                    fn($stop) => $stop->terminal_id != $validated['from_terminal_id']
                );

                // Merge into collection
                $routeStops = $routeStops->merge($filteredStops);
            }

            // ✅ DISTINCT: remove duplicates using terminal_id
            $uniqueStops = $routeStops
                ->unique('terminal_id')
                ->values()
                ->all();

            return response()->json(['route_stops' => $uniqueStops]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function getFare(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_terminal_id' => 'required|exists:terminals,id',
            'to_terminal_id' => 'required|exists:terminals,id',
        ]);

        try {
            if ($validated['from_terminal_id'] === $validated['to_terminal_id']) {
                throw new \Exception('From and To terminals must be different');
            }

            $fare = Fare::active()
                ->where('from_terminal_id', $validated['from_terminal_id'])
                ->where('to_terminal_id', $validated['to_terminal_id'])
                ->first();

            if (! $fare) {
                throw new \Exception('No fare found for this route segment');
            }

            return response()->json([
                'success' => true,
                'fare' => [
                    'id' => $fare->id,
                    'base_fare' => (float) $fare->base_fare,
                    'final_fare' => (float) $fare->final_fare,
                    'discount_type' => $fare->discount_type?->value,
                    'discount_value' => (float) $fare->discount_value,
                    'discount_amount' => $fare->getDiscountAmount(),
                    'currency' => $fare->currency,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    // public function getDepartureTimes(Request $request): JsonResponse
    // {
    //     $validated = $request->validate([
    //         'from_terminal_id' => 'required|exists:terminals,id',
    //         'to_terminal_id' => 'required|exists:terminals,id',
    //         'date' => 'required|date_format:Y-m-d|after_or_equal:today',
    //     ]);

    //     try {
    //         // Validate terminals are different
    //         if ($validated['from_terminal_id'] === $validated['to_terminal_id']) {
    //             throw new \Exception('From and To terminals must be different');
    //         }

    //         // Get timetable stops for the FROM terminal only on the given date
    //         $timetableStops = [];

    //         $timetableStopsQuery = TimetableStop::where('terminal_id', $validated['from_terminal_id'])
    //             ->where('is_active', true)
    //             ->with('timetable.route')
    //             ->get();

    //         foreach ($timetableStopsQuery as $ts) {
    //             // Verify timetable and route exist
    //             if (! $ts->timetable || ! $ts->timetable->route) {
    //                 continue;
    //             }

    //             // Check if route has the to_terminal in forward sequence
    //             $routeStops = RouteStop::where('route_id', $ts->timetable->route->id)
    //                 ->orderBy('sequence')
    //                 ->get();

    //             $fromStop = $routeStops->firstWhere('terminal_id', $validated['from_terminal_id']);
    //             $toStop = $routeStops->firstWhere('terminal_id', $validated['to_terminal_id']);

    //             // Skip if destination is not in forward sequence or doesn't exist
    //             if (! $fromStop || ! $toStop || $fromStop->sequence >= $toStop->sequence) {
    //                 continue;
    //             }

    //             // Combine date with departure_time to create full datetime
    //             if ($ts->departure_time) {
    //                 // Only include times that are in future
    //                 if (strtotime($ts->departure_time) >= time()) {
    //                     $timetableStops[] = [
    //                         'id' => $ts->id,
    //                         'departure_at' => $ts->departure_time,
    //                         'arrival_at' => $ts->arrival_time,
    //                         'terminal_id' => $ts->terminal_id,
    //                         'timetable_id' => $ts->timetable_id,
    //                         'route_id' => $ts->timetable->route->id,
    //                         'route_name' => $ts->timetable->route->name,
    //                     ];
    //                 }
    //             }
    //         }

    //         // Remove duplicates and sort by departure time
    //         $timetableStops = collect($timetableStops)
    //             ->unique('departure_at')
    //             ->sortBy('departure_at')
    //             ->values();

    //         return response()->json(['timetable_stops' => $timetableStops]);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 400);
    //     }
    // }

    public function getDepartureTimes(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_terminal_id' => 'required|exists:terminals,id',
            'to_terminal_id' => 'required|exists:terminals,id',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
        ]);

        try {
            if ($validated['from_terminal_id'] === $validated['to_terminal_id']) {
                throw new \Exception('From and To terminals must be different');
            }

            $selectedDate = $validated['date'];
            $now = now(); // current datetime

            $timetableStops = [];

            $timetableStopsQuery = TimetableStop::where('terminal_id', $validated['from_terminal_id'])
                ->where('is_active', true)
                ->with('timetable.route')
                ->get();

            foreach ($timetableStopsQuery as $ts) {

                if (! $ts->timetable || ! $ts->timetable->route) {
                    continue;
                }

                $routeStops = RouteStop::where('route_id', $ts->timetable->route->id)
                    ->orderBy('sequence')
                    ->get();

                $fromStop = $routeStops->firstWhere('terminal_id', $validated['from_terminal_id']);
                $toStop   = $routeStops->firstWhere('terminal_id', $validated['to_terminal_id']);

                if (! $fromStop || ! $toStop || $fromStop->sequence >= $toStop->sequence) {
                    continue;
                }

                if ($ts->departure_time) {

                    // ✅ Combine selected date WITH departure time
                    $fullDeparture = Carbon::parse(
                        $selectedDate . ' ' . $ts->departure_time
                    );

                    // ✅ Only allow future trips
                    if ($fullDeparture->greaterThanOrEqualTo($now)) {
                        $timetableStops[] = [
                            'id' => $ts->id,
                            'departure_at' => $ts->departure_time,
                            'arrival_at' => $ts->arrival_time,
                            'terminal_id' => $ts->terminal_id,
                            'timetable_id' => $ts->timetable_id,
                            'route_id' => $ts->timetable->route->id,
                            'route_name' => $ts->timetable->route->name,
                            'full_departure' => $fullDeparture->toDateTimeString(),
                        ];
                    }
                }
            }

            $timetableStops = collect($timetableStops)
                ->sortBy('full_departure')
                ->values();

            return response()->json(['timetable_stops' => $timetableStops]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function loadTripUpdated(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_terminal_id' => 'required|exists:terminals,id',
            'to_terminal_id' => 'required|exists:terminals,id',
            'timetable_id' => 'required|exists:timetables,id',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
        ]);

        try {
            // Get the timetable stop
            $timetable = Timetable::findOrFail($validated['timetable_id']);
            $route = $timetable->route;

            if (! $route) {
                throw new \Exception('Route not found for selected timetable');
            }

            // Check for existing trip - load stops separately
            $trip = Trip::where('timetable_id', $timetable->id)
                ->whereDate('departure_date', $validated['date'])
                ->first();

            // Create trip if not exists
            if (! $trip) {
                $trip = $this->tripFactory->createFromTimetable($timetable->id, $validated['date']);
            }

            // Load the stops relationship
            $trip->load('stops');

            // Get route stops for the segment
            $routeStops = RouteStop::where('route_id', $route->id)
                ->with('terminal:id,name,code')
                ->orderBy('sequence')
                ->get();

            $fromStop = $routeStops->firstWhere('terminal_id', $validated['from_terminal_id']);
            $toStop = $routeStops->firstWhere('terminal_id', $validated['to_terminal_id']);

            if (! $fromStop || ! $toStop || $fromStop->sequence >= $toStop->sequence) {
                throw new \Exception('Invalid segment selection');
            }

            // Map to trip stops
            $tripFromStop = $trip->stops->firstWhere('terminal_id', $validated['from_terminal_id']);
            $tripToStop = $trip->stops->firstWhere('terminal_id', $validated['to_terminal_id']);

            if (! $tripFromStop || ! $tripToStop) {
                throw new \Exception('Selected terminals not found in trip');
            }

            // Get seat map
            $seatCount = $this->availabilityService->seatCount($trip);
            $availableSeats = $this->availabilityService->availableSeats(
                $trip->id,
                $tripFromStop->id,
                $tripToStop->id
            );

            $seatMap = $this->buildSeatMap($trip, $tripFromStop, $tripToStop, $seatCount, $availableSeats);

            return response()->json([
                'trip' => $trip->load('bus'),
                'route' => $route->only(['id', 'name', 'code']),
                'from_stop' => $tripFromStop->only(['id', 'terminal_id', 'departure_at', 'sequence']),
                'to_stop' => $tripToStop->only(['id', 'terminal_id', 'arrival_at', 'sequence']),
                'seat_map' => $seatMap,
                'available_count' => count($availableSeats),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function lockSeats(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'seat_numbers' => 'required|array|min:1',
            'seat_numbers.*' => 'integer|min:1|max:44',
            'from_stop_id' => 'required|exists:trip_stops,id',
            'to_stop_id' => 'required|exists:trip_stops,id',
        ]);

        try {
            // Lock the row for the specified trip to prevent race conditions when booking/locking seats
            $trip = Trip::lockForUpdate()->findOrFail($validated['trip_id']);
            $availableSeats = $this->availabilityService->availableSeats(
                $trip->id,
                $validated['from_stop_id'],
                $validated['to_stop_id']
            );

            // Check all seats are available
            $availableSet = array_flip($availableSeats);
            foreach ($validated['seat_numbers'] as $seat) {
                if (! isset($availableSet[$seat])) {
                    throw ValidationException::withMessages([
                        'seats' => "Seat {$seat} is not available.",
                    ]);
                }
            }

            // Broadcast seat locked event
            SeatLocked::dispatch(
                $trip->id,
                $validated['seat_numbers'],
                Auth::user()
            );

            return response()->json([
                'message' => 'Seats locked successfully',
                'locked_seats' => $validated['seat_numbers'],
                'trip_id' => $trip->id,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function unlockSeats(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'seat_numbers' => 'required|array|min:1',
            'seat_numbers.*' => 'integer',
        ]);

        SeatUnlocked::dispatch(
            $validated['trip_id'],
            $validated['seat_numbers'],
            Auth::user()
        );

        return response()->json([
            'message' => 'Seats unlocked successfully',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'from_terminal_id' => 'required|exists:terminals,id',
            'to_terminal_id' => 'required|exists:terminals,id',
            'seat_numbers' => 'required|array|min:1',
            'seat_numbers.*' => 'integer',
            'seats_data' => 'nullable|json', // Optional: seats with gender information
            'passengers' => 'required|json',
            'channel' => 'required|in:counter,phone,online',
            'payment_method' => 'nullable|in:cash,card,mobile_wallet,bank_transfer',
            'transaction_id' => 'nullable|string|max:100',
            'amount_received' => 'nullable|numeric|min:0',
            'fare_per_seat' => 'required|numeric|min:0',
            'total_fare' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'final_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            // Validate transaction ID for non-cash counter payments
            if ($validated['channel'] === 'counter' && $validated['payment_method'] !== 'cash' && empty($validated['transaction_id'])) {
                throw new \Exception('Transaction ID is required for non-cash payments');
            }

            // Parse passengers JSON
            $passengers = json_decode($validated['passengers'], true);
            if (! is_array($passengers) || count($passengers) === 0) {
                throw new \Exception('Invalid passengers data');
            }

            // Parse seats data JSON (optional - contains seat_number and gender for each seat)
            $seatsData = [];
            if (! empty($validated['seats_data'])) {
                $seatsData = json_decode($validated['seats_data'], true);
                if (! is_array($seatsData)) {
                    $seatsData = [];
                }
            }

            // Get trip stops
            $trip = Trip::findOrFail($validated['trip_id']);
            $fromTerminalId = $validated['from_terminal_id'];
            $toTerminalId = $validated['to_terminal_id'];

            $fromStop = $trip->stops()->where('terminal_id', $fromTerminalId)->firstOrFail();
            $toStop = $trip->stops()->where('terminal_id', $toTerminalId)->firstOrFail();

            if ($fromStop->sequence >= $toStop->sequence) {
                throw new \Exception('Invalid segment selection');
            }

            $data = [
                'trip_id' => $validated['trip_id'],
                'from_stop_id' => $fromStop->id,
                'to_stop_id' => $toStop->id,
                'seat_numbers' => $validated['seat_numbers'],
                'seats_data' => $seatsData, // Seats with gender information
                'passengers' => $passengers, // Passenger information (without seat_number)
                'channel' => $validated['channel'],
                'payment_method' => $validated['payment_method'] ?? 'cash',
                'online_transaction_id' => $validated['transaction_id'] ?? null,
                'total_fare' => $validated['total_fare'],
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'tax_amount' => $validated['tax_amount'] ?? 0,
                'final_amount' => $validated['final_amount'],
                'notes' => $validated['notes'] ?? null,
                'user_id' => Auth::user()->id,
            ];

            // Handle payment for counter bookings
            if ($validated['channel'] === 'counter') {
                $amountReceived = $validated['amount_received'] ?? 0;
                $returnAmount = max(0, $amountReceived - $validated['final_amount']);

                $data['payment_received_from_customer'] = $amountReceived;
                $data['return_after_deduction_from_customer'] = $returnAmount;
            } else {
                // For phone and online bookings, set default payment values
                $data['payment_received_from_customer'] = 0;
                $data['return_after_deduction_from_customer'] = 0;
            }

            // Create booking
            $booking = $this->bookingService->create($data, Auth::user());

            // Broadcast seat confirmed event
            foreach ($validated['seat_numbers'] as $seat) {
                SeatConfirmed::dispatch($validated['trip_id'], [$seat], Auth::user());
            }

            return response()->json([
                'booking' => [
                    'id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'status' => $booking->status,
                    'total_fare' => $booking->total_fare,
                    'discount_amount' => $booking->discount_amount,
                    'tax_amount' => $booking->tax_amount,
                    'final_amount' => $booking->final_amount,
                    'payment_method' => $booking->payment_method,
                    'transaction_id' => $booking->online_transaction_id,
                    'seats' => $booking->seats->pluck('seat_number')->toArray(),
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    private function buildSeatMap(Trip $trip, TripStop $fromStop, TripStop $toStop, int $total, array $available): array
    {
        $seatMap = [];
        $bookedSeats = $this->getBookedSeats($trip, $fromStop, $toStop);

        for ($i = 1; $i <= $total; $i++) {
            $seatMap[$i] = [
                'number' => $i,
                'status' => 'available', // available, booked, held
            ];

            if (isset($bookedSeats[$i])) {
                $seatMap[$i]['status'] = $bookedSeats[$i]['status'];
                $seatMap[$i]['booking_id'] = $bookedSeats[$i]['booking_id'];
                $seatMap[$i]['gender'] = $bookedSeats[$i]['gender'];
            } elseif (! in_array($i, $available)) {
                $seatMap[$i]['status'] = 'held';
            }
        }

        return $seatMap;
    }

    private function getBookedSeats(Trip $trip, TripStop $fromStop, TripStop $toStop): array
    {
        $bookings = Booking::with(['seats', 'passengers'])
            ->where('trip_id', $trip->id)
            ->whereIn('status', ['confirmed', 'checked_in', 'boarded'])
            ->get();

        $bookedSeats = [];
        foreach ($bookings as $booking) {
            // Check if segment overlaps
            $bookingFrom = $booking->fromStop->sequence;
            $bookingTo = $booking->toStop->sequence;
            $queryFrom = $fromStop->sequence;
            $queryTo = $toStop->sequence;

            if ($bookingFrom < $queryTo && $queryFrom < $bookingTo) {
                foreach ($booking->seats as $seat) {
                    // Find matching passenger for this seat
                    $passenger = $booking->passengers->first(function ($p) use ($seat) {
                        return $p->booking_id === $seat->booking_id;
                    });

                    $bookedSeats[$seat->seat_number] = [
                        'status' => $booking->status === 'hold' ? 'held' : 'booked',
                        'booking_id' => $booking->id,
                        'gender' => $passenger?->gender ?? $seat->gender ?? null,
                    ];
                }
            }
        }

        return $bookedSeats;
    }

    public function listBuses(): JsonResponse
    {
        try {
            $buses = \App\Models\Bus::where('status', 'active')
                ->select('id', 'name', 'registration_number', 'model', 'color')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'buses' => $buses,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function assignBusDriver(Request $request, $tripId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'bus_id' => 'required|exists:buses,id',
                'driver_name' => 'required|string|max:255',
                'driver_phone' => 'required|string|max:20',
                'driver_cnic' => 'required|string|max:50',
                'driver_license' => 'required|string|max:100',
                'driver_address' => 'nullable|string|max:500',
            ]);

            $trip = Trip::findOrFail($tripId);

            // Update trip with bus and driver information
            $trip->update([
                'bus_id' => $validated['bus_id'],
                'driver_name' => $validated['driver_name'],
                'driver_phone' => $validated['driver_phone'],
                'driver_cnic' => $validated['driver_cnic'],
                'driver_license' => $validated['driver_license'],
                'driver_address' => $validated['driver_address'] ?? null,
            ]);

            // Load bus relationship
            $trip->load('bus');

            return response()->json([
                'success' => true,
                'message' => 'Bus and driver assigned successfully!',
                'trip' => $trip,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
