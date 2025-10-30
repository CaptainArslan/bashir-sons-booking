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
        // TODO: Implement getData for bookings table
        return response()->json(['data' => []]);
    }

    public function create(): View
    {
        return view('admin.bookings.create');
    }

    public function show(Booking $booking)
    {
        return view('admin.bookings.show', compact('booking'));
    }

    public function edit(Booking $booking): View
    {
        return view('admin.bookings.edit', compact('booking'));
    }

    public function update(Request $request, Booking $booking)
    {
        // TODO: Implement update
        return redirect()->route('admin.bookings.index');
    }

    public function destroy(Booking $booking)
    {
        // TODO: Implement destroy
        return response()->json(['message' => 'Booking deleted']);
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
                    $passenger = $booking->passengers()->find(function ($p) use ($seat) {
                        return $p->booking_id === $seat->booking_id;
                    });
                    $bookedSeats[$seat->seat_number] = [
                        'status' => $booking->status === 'hold' ? 'held' : 'booked',
                        'booking_id' => $booking->id,
                        'gender' => $passenger?->gender ?? null,
                    ];
                }
            }
        }

        return $bookedSeats;
    }
}
