<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentMethodEnum;
use App\Events\SeatConfirmed;
use App\Events\SeatLocked;
use App\Events\SeatUnlocked;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Fare;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\Terminal;
use App\Models\TimetableStop;
use App\Models\Trip;
use App\Models\TripStop;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use App\Services\TripFactoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            $query->where('booking_number', 'like', '%'.$request->booking_number.'%');
        }

        return datatables()
            ->eloquent($query)
            ->addColumn('booking_number', function (Booking $booking) {
                return '<span class="badge bg-primary">#'.$booking->booking_number.'</span>';
            })
            ->addColumn('created_at', function (Booking $booking) {
                return $booking->created_at->format('d M Y, H:i');
            })
            ->addColumn('route', function (Booking $booking) {
                $from = $booking->fromStop?->terminal?->code ?? 'N/A';
                $to = $booking->toStop?->terminal?->code ?? 'N/A';

                return '<strong>'.$from.' → '.$to.'</strong>';
            })
            ->addColumn('seats', function (Booking $booking) {
                $seatNumbers = $booking->seats->pluck('seat_number')->join(', ');

                return '<span class="badge bg-info">'.$seatNumbers.'</span>';
            })
            ->addColumn('passengers_count', function (Booking $booking) {
                return '<span class="badge bg-secondary">'.$booking->passengers->count().' passengers</span>';
            })
            ->addColumn('amount', function (Booking $booking) {
                return '<strong>PKR '.number_format($booking->final_amount, 2).'</strong>';
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

                return '<span class="badge '.$badgeClass.'">'.ucfirst($booking->status).'</span>';
            })
            ->addColumn('payment_status', function (Booking $booking) {
                $badgeClass = match ($booking->payment_status) {
                    'paid' => 'bg-success',
                    'unpaid' => 'bg-danger',
                    'partial' => 'bg-warning',
                    default => 'bg-secondary',
                };

                return '<span class="badge '.$badgeClass.'">'.ucfirst($booking->payment_status).'</span>';
            })
            ->addColumn('actions', function (Booking $booking) {
                return '
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary" onclick="viewBookingDetails('.$booking->id.')">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <a href="'.route('admin.bookings.edit', $booking->id).'" class="btn btn-outline-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <button type="button" class="btn btn-outline-danger" onclick="deleteBooking('.$booking->id.')">
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
        return view('admin.bookings.edit', compact('booking'));
    }

    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => 'required|in:confirmed,hold,checked_in,boarded,cancelled',
            'payment_status' => 'required|in:paid,unpaid,partial',
            'payment_method' => 'nullable|in:cash,card,mobile_wallet,bank_transfer',
            'online_transaction_id' => 'nullable|string|max:100',
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
            // Update basic booking information
            $booking->update([
                'status' => $validated['status'],
                'payment_status' => $validated['payment_status'],
                'payment_method' => $validated['payment_method'] ?? 'cash',
                'online_transaction_id' => $validated['online_transaction_id'] ?? null,
                'reserved_until' => $validated['status'] === 'hold' ? ($validated['reserved_until'] ?? now()->addMinutes(15)) : null,
                'notes' => $validated['notes'] ?? null,
            ]);

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

    /**
     * GET /admin/bookings/console/trip-passengers/{tripId}
     * Fetch all passengers for a trip with their booking details
     */
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
                foreach ($booking->passengers as $passenger) {
                    // Find the seat number for this passenger
                    $seatNumber = $booking->seats->first()?->seat_number;

                    $passengers[] = [
                        'id' => $passenger->id,
                        'booking_id' => $booking->id,
                        'name' => $passenger->name,
                        'gender' => $passenger->gender,
                        'age' => $passenger->age,
                        'cnic' => $passenger->cnic,
                        'phone' => $passenger->phone,
                        'email' => $passenger->email,
                        'seat_number' => $seatNumber,
                        'from_stop' => $booking->fromStop?->terminal?->name ?? 'N/A',
                        'from_code' => $booking->fromStop?->terminal?->code ?? 'N/A',
                        'to_stop' => $booking->toStop?->terminal?->name ?? 'N/A',
                        'to_code' => $booking->toStop?->terminal?->code ?? 'N/A',
                        'status' => $booking->status,
                        'payment_status' => $booking->payment_status,
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

    /**
     * GET /admin/bookings/console/booking-details/{bookingId}
     * Fetch complete booking details for modal display
     */
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
                    'passengers' => $booking->passengers->map(fn ($p) => [
                        'name' => $p->name,
                        'age' => $p->age,
                        'gender' => $p->gender,
                        'cnic' => $p->cnic,
                        'phone' => $p->phone,
                        'email' => $p->email,
                        'seat_number' => $booking->seats->where('id', '!=', null)->first()?->seat_number,
                    ])->toArray(),
                    'seats' => $booking->seats->map(fn ($s) => [
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

    /**
     * GET /admin/bookings/terminals
     * Fetch terminals for the current user (employee or admin)
     */
    public function getTerminals(Request $request): JsonResponse
    {
        $user = auth()->user();
        $terminals = Terminal::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'city_id', 'code']);

        return response()->json(['terminals' => $terminals]);
    }

    /**
     * GET /admin/bookings/routes?terminal_id={id}
     * Fetch routes for a given terminal
     */
    public function getRoutes(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'terminal_id' => 'required|exists:terminals,id',
        ]);

        $routes = Route::query()
            ->whereHas('routeStops', fn ($q) => $q->where('terminal_id', $validated['terminal_id']))
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'direction', 'base_currency']);

        return response()->json(['routes' => $routes]);
    }

    /**
     * GET /admin/bookings/stops?route_id={id}
     * Fetch stops (terminals) for a given route
     */
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

    /**
     * GET /admin/bookings/console/route-stops?from_terminal_id={id}
     * Fetch route stops after a given terminal
     */
    public function getRouteStops(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_terminal_id' => 'required|exists:terminals,id',
        ]);

        try {
            // Find all routes that have this terminal, then get forward stops
            $routes = Route::query()
                ->whereHas('routeStops', fn ($q) => $q->where('terminal_id', $validated['from_terminal_id']))
                ->where('status', 'active')
                ->get();

            $routeStops = [];
            foreach ($routes as $route) {
                // Get all stops for this route
                $stops = RouteStop::where('route_id', $route->id)
                    ->with('terminal:id,name,code')
                    ->orderBy('sequence')
                    ->get();

                // Find the from terminal sequence
                $fromTerminalSequence = $stops->firstWhere('terminal_id', $validated['from_terminal_id'])?->sequence;

                if ($fromTerminalSequence === null) {
                    continue;
                }

                // Get only forward stops (sequence > from_terminal sequence)
                foreach ($stops as $stop) {
                    if ($stop->sequence > $fromTerminalSequence) {
                        $routeStops[] = $stop;
                    }
                }
            }

            return response()->json(['route_stops' => $routeStops]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * GET /admin/bookings/console/fare
     * Fetch fare for a terminal segment
     */
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

    /**
     * GET /admin/bookings/console/departure-times
     * Fetch available departure times for a route segment on a given date
     */
    public function getDepartureTimes(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_terminal_id' => 'required|exists:terminals,id',
            'to_terminal_id' => 'required|exists:terminals,id',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
        ]);

        try {
            // Validate terminals are different
            if ($validated['from_terminal_id'] === $validated['to_terminal_id']) {
                throw new \Exception('From and To terminals must be different');
            }

            // Get timetable stops for the FROM terminal only on the given date
            $timetableStops = [];

            $timetableStopsQuery = TimetableStop::where('terminal_id', $validated['from_terminal_id'])
                ->where('is_active', true)
                ->with('timetable.route')
                ->get();

            foreach ($timetableStopsQuery as $ts) {
                // Verify timetable and route exist
                if (! $ts->timetable || ! $ts->timetable->route) {
                    continue;
                }

                // Check if route has the to_terminal in forward sequence
                $routeStops = RouteStop::where('route_id', $ts->timetable->route->id)
                    ->orderBy('sequence')
                    ->get();

                $fromStop = $routeStops->firstWhere('terminal_id', $validated['from_terminal_id']);
                $toStop = $routeStops->firstWhere('terminal_id', $validated['to_terminal_id']);

                // Skip if destination is not in forward sequence or doesn't exist
                if (! $fromStop || ! $toStop || $fromStop->sequence >= $toStop->sequence) {
                    continue;
                }

                // Combine date with departure_time to create full datetime
                if ($ts->departure_time) {
                    $departureDateTime = $validated['date'].' '.$ts->departure_time;

                    // Only include times that are in future
                    if (strtotime($departureDateTime) >= time()) {
                        $timetableStops[] = [
                            'id' => $ts->id,
                            'departure_at' => $departureDateTime,
                            'terminal_id' => $ts->terminal_id,
                            'timetable_id' => $ts->timetable_id,
                            'route_id' => $ts->timetable->route->id,
                            'route_name' => $ts->timetable->route->name,
                        ];
                    }
                }
            }

            // Remove duplicates and sort by departure time
            $timetableStops = collect($timetableStops)
                ->unique('departure_at')
                ->sortBy('departure_at')
                ->values();

            return response()->json(['timetable_stops' => $timetableStops]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * POST /admin/bookings/load-trip (UPDATED)
     * Load or create trip using timetable_stop_id
     */
    public function loadTripUpdated(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_terminal_id' => 'required|exists:terminals,id',
            'to_terminal_id' => 'required|exists:terminals,id',
            'timetable_stop_id' => 'required|exists:timetable_stops,id',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
        ]);

        try {
            // Get the timetable stop
            $timetableStop = TimetableStop::findOrFail($validated['timetable_stop_id']);
            $timetable = $timetableStop->timetable;

            if (! $timetable) {
                throw new \Exception('Timetable not found for selected stop');
            }

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
                'trip' => $trip->only(['id', 'departure_datetime', 'estimated_arrival_datetime']),
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

    /**
     * POST /admin/bookings/lock-seats
     * Lock selected seats
     */
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
                auth()->user()
            );

            return response()->json([
                'message' => 'Seats locked successfully',
                'locked_seats' => $validated['seat_numbers'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * POST /admin/bookings/unlock-seats
     * Unlock seats (user deselection)
     */
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
            auth()->user()
        );

        return response()->json([
            'message' => 'Seats unlocked successfully',
        ]);
    }

    /**
     * POST /admin/bookings/store
     * Create booking
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'from_terminal_id' => 'required|exists:terminals,id',
            'to_terminal_id' => 'required|exists:terminals,id',
            'seat_numbers' => 'required|array|min:1',
            'seat_numbers.*' => 'integer',
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
                'passengers' => $passengers,
                'channel' => $validated['channel'],
                'payment_method' => $validated['payment_method'] ?? 'cash',
                'online_transaction_id' => $validated['transaction_id'] ?? null,
                'total_fare' => $validated['total_fare'],
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'tax_amount' => $validated['tax_amount'] ?? 0,
                'final_amount' => $validated['final_amount'],
                'notes' => $validated['notes'] ?? null,
                'user_id' => auth()->id(),
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
            $booking = $this->bookingService->create($data, auth()->user());

            // Broadcast seat confirmed event
            foreach ($validated['seat_numbers'] as $seat) {
                SeatConfirmed::dispatch($validated['trip_id'], [$seat], auth()->user());
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

    /**
     * Build seat map with status
     */
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

    /**
     * Get booked seats for segment
     */
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

    /**
     * List all available buses
     */
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

    /**
     * Assign bus and driver to a trip
     */
    public function assignBusDriver(Request $request, int $tripId): JsonResponse
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
