<?php

namespace App\Http\Controllers\Admin;

use App\Events\SeatConfirmed;
use App\Events\SeatLocked;
use App\Events\SeatUnlocked;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\Terminal;
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
        return view('admin.bookings.console');
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
     * POST /admin/bookings/load-trip
     * Load or create trip and return seat map
     */
    public function loadTrip(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'route_id' => 'required|exists:routes,id',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'from_stop_id' => 'required|exists:trip_stops,id|integer',
            'to_stop_id' => 'required|exists:trip_stops,id|integer',
        ]);

        try {
            $route = Route::with(['routeStops', 'timetables'])->findOrFail($validated['route_id']);
            $date = $validated['date'];

            // Check for existing trip
            $trip = Trip::where('route_id', $route->id)
                ->whereDate('departure_date', $date)
                ->with(['stops', 'bus'])
                ->first();

            // Create trip if not exists
            if (! $trip && $route->timetables()->exists()) {
                $timetable = $route->timetables()->first();
                $trip = $this->tripFactory->createFromTimetable($timetable->id, $date);
                $trip->load(['stops', 'bus']);
            }

            if (! $trip) {
                throw ValidationException::withMessages([
                    'trip' => 'No timetable configured for this route. Please create one first.',
                ]);
            }

            // Validate from/to stops exist in this trip
            $fromStop = $trip->stops()->whereKey($validated['from_stop_id'])->first();
            $toStop = $trip->stops()->whereKey($validated['to_stop_id'])->first();

            if (! $fromStop || ! $toStop || $fromStop->sequence >= $toStop->sequence) {
                throw ValidationException::withMessages([
                    'stops' => 'Invalid stop selection or invalid sequence.',
                ]);
            }

            // Get seat map
            $seatCount = $this->availabilityService->seatCount($trip);
            $availableSeats = $this->availabilityService->availableSeats(
                $trip->id,
                $fromStop->id,
                $toStop->id
            );

            // Build seat map with booking info
            $seatMap = $this->buildSeatMap($trip, $fromStop, $toStop, $seatCount, $availableSeats);

            return response()->json([
                'trip' => $trip->only(['id', 'departure_datetime', 'estimated_arrival_datetime']),
                'from_stop' => $fromStop->only(['id', 'terminal_id', 'departure_at', 'sequence']),
                'to_stop' => $toStop->only(['id', 'terminal_id', 'arrival_at', 'sequence']),
                'seat_map' => $seatMap,
                'available_count' => count($availableSeats),
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
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
            'from_stop_id' => 'required|exists:trip_stops,id',
            'to_stop_id' => 'required|exists:trip_stops,id',
            'seat_numbers' => 'required|array|min:1',
            'seat_numbers.*' => 'integer',
            'passengers' => 'required|array|min:1',
            'passengers.*.name' => 'required|string',
            'passengers.*.gender' => 'in:male,female',
            'channel' => 'required|in:counter,phone,online',
            'payment_method' => 'in:cash,card,online',
            'amount_received' => 'nullable|numeric|min:0',
            'total_fare' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'final_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'terminal_id' => 'nullable|exists:terminals,id',
        ]);

        try {
            $data = [
                'trip_id' => $validated['trip_id'],
                'from_stop_id' => $validated['from_stop_id'],
                'to_stop_id' => $validated['to_stop_id'],
                'seat_numbers' => $validated['seat_numbers'],
                'passengers' => $validated['passengers'],
                'channel' => $validated['channel'],
                'payment_method' => $validated['payment_method'] ?? 'none',
                'total_fare' => $validated['total_fare'],
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'tax_amount' => $validated['tax_amount'] ?? 0,
                'final_amount' => $validated['final_amount'],
                'notes' => $validated['notes'] ?? null,
                'terminal_id' => $validated['terminal_id'] ?? null,
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
                'message' => 'Booking created successfully',
                'booking' => [
                    'id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'status' => $booking->status,
                    'total_fare' => $booking->total_fare,
                    'discount_amount' => $booking->discount_amount,
                    'tax_amount' => $booking->tax_amount,
                    'final_amount' => $booking->final_amount,
                    'payment_method' => $booking->payment_method,
                    'seats' => $booking->seats->pluck('seat_number')->toArray(),
                    'passengers' => $booking->passengers->toArray(),
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
