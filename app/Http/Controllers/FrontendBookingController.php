<?php

namespace App\Http\Controllers;

use App\Models\Fare;
use App\Models\RouteStop;
use App\Models\TimetableStop;
use App\Models\Trip;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use App\Services\TripFactoryService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class FrontendBookingController extends Controller
{
    public function __construct(
        private AvailabilityService $availabilityService,
        private BookingService $bookingService,
        private TripFactoryService $tripFactory
    ) {}

    public function showTrips(Request $request): View
    {
        $validated = $request->validate([
            'from_terminal_id' => 'required|exists:terminals,id',
            'to_terminal_id' => 'required|exists:terminals,id',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'passengers' => 'nullable|integer|min:1|max:10',
        ]);

        $fromTerminal = \App\Models\Terminal::with('city')->findOrFail($validated['from_terminal_id']);
        $toTerminal = \App\Models\Terminal::with('city')->findOrFail($validated['to_terminal_id']);

        return view('frontend.bookings.trips', [
            'from_terminal_id' => $validated['from_terminal_id'],
            'to_terminal_id' => $validated['to_terminal_id'],
            'from_terminal' => $fromTerminal,
            'to_terminal' => $toTerminal,
            'date' => $validated['date'],
            'passengers' => $validated['passengers'] ?? 1,
        ]);
    }

    public function loadTrips(Request $request): JsonResponse
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
            $now = now();

            $trips = [];

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
                $toStop = $routeStops->firstWhere('terminal_id', $validated['to_terminal_id']);

                if (! $fromStop || ! $toStop || $fromStop->sequence >= $toStop->sequence) {
                    continue;
                }

                if ($ts->departure_time) {
                    $fullDeparture = Carbon::parse(
                        $selectedDate.' '.$ts->departure_time
                    );

                    if ($fullDeparture->greaterThanOrEqualTo($now)) {
                        // Get or create trip
                        $trip = Trip::where('timetable_id', $ts->timetable_id)
                            ->whereDate('departure_date', $selectedDate)
                            ->first();

                        if (! $trip) {
                            $trip = $this->tripFactory->createFromTimetable($ts->timetable_id, $selectedDate);
                        }

                        // Get fare
                        $fare = Fare::active()
                            ->where('from_terminal_id', $validated['from_terminal_id'])
                            ->where('to_terminal_id', $validated['to_terminal_id'])
                            ->first();

                        // Get available seats count
                        $trip->load('stops');
                        $tripFromStop = $trip->stops->firstWhere('terminal_id', $validated['from_terminal_id']);
                        $tripToStop = $trip->stops->firstWhere('terminal_id', $validated['to_terminal_id']);

                        $availableSeats = 0;
                        if ($tripFromStop && $tripToStop) {
                            $availableSeats = count($this->availabilityService->availableSeats(
                                $trip->id,
                                $tripFromStop->id,
                                $tripToStop->id
                            ));
                        }

                        $trips[] = [
                            'trip_id' => $trip->id,
                            'timetable_id' => $ts->timetable_id,
                            'route_id' => $ts->timetable->route->id,
                            'route_name' => $ts->timetable->route->name,
                            'departure_time' => $ts->departure_time,
                            'arrival_time' => $ts->arrival_time,
                            'departure_datetime' => $fullDeparture->toDateTimeString(),
                            'fare' => $fare ? [
                                'final_fare' => (float) $fare->final_fare,
                                'currency' => $fare->currency,
                            ] : null,
                            'available_seats' => $availableSeats,
                            'bus_name' => $trip->bus?->name ?? 'TBA',
                        ];
                    }
                }
            }

            $trips = collect($trips)->sortBy('departure_datetime')->values();

            return response()->json(['trips' => $trips]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function selectSeats(Request $request): View
    {
        $validated = $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'from_terminal_id' => 'required|exists:terminals,id',
            'to_terminal_id' => 'required|exists:terminals,id',
            'date' => 'required|date_format:Y-m-d',
            'passengers' => 'required|integer|min:1|max:10',
        ]);

        return view('frontend.bookings.seats', [
            'trip_id' => $validated['trip_id'],
            'from_terminal_id' => $validated['from_terminal_id'],
            'to_terminal_id' => $validated['to_terminal_id'],
            'date' => $validated['date'],
            'passengers' => $validated['passengers'],
        ]);
    }

    public function loadTripDetails(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'from_terminal_id' => 'required|exists:terminals,id',
            'to_terminal_id' => 'required|exists:terminals,id',
        ]);

        try {
            $trip = Trip::with(['stops.terminal', 'route', 'bus'])->findOrFail($validated['trip_id']);

            $tripFromStop = $trip->stops->firstWhere('terminal_id', $validated['from_terminal_id']);
            $tripToStop = $trip->stops->firstWhere('terminal_id', $validated['to_terminal_id']);

            if (! $tripFromStop || ! $tripToStop) {
                throw new \Exception('Selected terminals not found in trip');
            }

            if ($tripFromStop->sequence >= $tripToStop->sequence) {
                throw new \Exception('Invalid segment selection');
            }

            // Get fare
            $fare = Fare::active()
                ->where('from_terminal_id', $validated['from_terminal_id'])
                ->where('to_terminal_id', $validated['to_terminal_id'])
                ->first();

            if (! $fare) {
                throw new \Exception('No fare found for this route segment');
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
                'trip' => [
                    'id' => $trip->id,
                    'route_name' => $trip->route?->name,
                    'bus_name' => $trip->bus?->name ?? 'TBA',
                    'departure_date' => $trip->departure_date->format('Y-m-d'),
                    'departure_datetime' => $trip->departure_datetime?->format('Y-m-d H:i:s'),
                ],
                'from_stop' => [
                    'terminal_name' => $tripFromStop->terminal?->name,
                    'terminal_code' => $tripFromStop->terminal?->code,
                    'departure_at' => $tripFromStop->departure_at?->format('Y-m-d H:i:s'),
                ],
                'to_stop' => [
                    'terminal_name' => $tripToStop->terminal?->name,
                    'terminal_code' => $tripToStop->terminal?->code,
                    'arrival_at' => $tripToStop->arrival_at?->format('Y-m-d H:i:s'),
                ],
                'fare' => [
                    'final_fare' => (float) $fare->final_fare,
                    'currency' => $fare->currency,
                    'discount_type' => $fare->discount_type?->value,
                    'discount_value' => (float) $fare->discount_value,
                ],
                'seat_map' => $seatMap,
                'available_count' => count($availableSeats),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function store(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $validated = $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'from_terminal_id' => 'required|exists:terminals,id',
            'to_terminal_id' => 'required|exists:terminals,id',
            'seat_numbers' => 'required|array|min:1',
            'seat_numbers.*' => 'integer',
            'seats_data' => 'required|json',
            'passengers' => 'required|json',
            'total_fare' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'final_amount' => 'required|numeric|min:0',
        ]);

        try {
            // Parse passengers and seats data
            $passengers = json_decode($validated['passengers'], true);
            $seatsData = json_decode($validated['seats_data'], true);

            if (! is_array($passengers) || count($passengers) === 0) {
                throw new \Exception('Invalid passengers data');
            }

            if (! is_array($seatsData) || count($seatsData) === 0) {
                throw new \Exception('Invalid seats data');
            }

            // Get trip stops
            $trip = Trip::with('route')->findOrFail($validated['trip_id']);
            $fromTerminalId = $validated['from_terminal_id'];
            $toTerminalId = $validated['to_terminal_id'];

            $tripFromStop = $trip->stops()->where('terminal_id', $fromTerminalId)->firstOrFail();
            $tripToStop = $trip->stops()->where('terminal_id', $toTerminalId)->firstOrFail();

            if ($tripFromStop->sequence >= $tripToStop->sequence) {
                throw new \Exception('Invalid segment selection');
            }

            // Find RouteStop IDs
            $fromRouteStop = RouteStop::where('route_id', $trip->route_id)
                ->where('terminal_id', $fromTerminalId)
                ->where('sequence', $tripFromStop->sequence)
                ->firstOrFail();

            $toRouteStop = RouteStop::where('route_id', $trip->route_id)
                ->where('terminal_id', $toTerminalId)
                ->where('sequence', $tripToStop->sequence)
                ->firstOrFail();

            $data = [
                'trip_id' => $validated['trip_id'],
                'from_stop_id' => $fromRouteStop->id,
                'to_stop_id' => $toRouteStop->id,
                'from_trip_stop_id' => $tripFromStop->id,
                'to_trip_stop_id' => $tripToStop->id,
                'terminal_id' => $fromTerminalId,
                'seat_numbers' => $validated['seat_numbers'],
                'seats_data' => $seatsData,
                'passengers' => $passengers,
                'channel' => 'online',
                'payment_method' => 'mobile_wallet', // Will be updated after payment
                'online_transaction_id' => null,
                'total_fare' => $validated['total_fare'],
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'tax_amount' => $validated['tax_amount'] ?? 0,
                'final_amount' => $validated['final_amount'],
                'notes' => null,
                'user_id' => Auth::user()->id,
                'payment_received_from_customer' => 0,
                'return_after_deduction_from_customer' => 0,
                'status' => 'hold', // Set as hold until payment
                'reserved_until' => now()->addMinutes(15), // Expire after 15 minutes
                'payment_status' => 'unpaid',
            ];

            // Create booking with hold status
            $booking = $this->bookingService->create($data, Auth::user());

            return response()->json([
                'success' => true,
                'booking' => [
                    'id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'status' => $booking->status,
                    'final_amount' => $booking->final_amount,
                    'reserved_until' => $booking->reserved_until?->format('Y-m-d H:i:s'),
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

    private function buildSeatMap(Trip $trip, $fromStop, $toStop, int $total, array $available): array
    {
        $seatMap = [];
        $bookedSeats = $this->getBookedSeats($trip, $fromStop, $toStop);

        for ($i = 1; $i <= $total; $i++) {
            $seatMap[$i] = [
                'number' => $i,
                'status' => 'available',
            ];

            if (isset($bookedSeats[$i])) {
                $seatMap[$i]['status'] = $bookedSeats[$i]['status'];
                $seatMap[$i]['gender'] = $bookedSeats[$i]['gender'];
            } elseif (! in_array($i, $available)) {
                $seatMap[$i]['status'] = 'held';
            }
        }

        return $seatMap;
    }

    private function getBookedSeats(Trip $trip, $fromStop, $toStop): array
    {
        $bookings = \App\Models\Booking::with(['seats', 'passengers', 'fromStop', 'toStop'])
            ->where('trip_id', $trip->id)
            ->whereIn('status', ['confirmed', 'checked_in', 'boarded'])
            ->get();

        $bookedSeats = [];

        $queryFrom = $fromStop->sequence ?? null;
        $queryTo = $toStop->sequence ?? null;

        if ($queryFrom === null || $queryTo === null) {
            return $bookedSeats;
        }

        foreach ($bookings as $booking) {
            if (! $booking->fromStop || ! $booking->toStop) {
                continue;
            }

            $bookingFrom = $booking->fromStop->sequence ?? null;
            $bookingTo = $booking->toStop->sequence ?? null;

            if ($bookingFrom === null || $bookingTo === null) {
                continue;
            }

            if ($bookingFrom < $queryTo && $queryFrom < $bookingTo) {
                foreach ($booking->seats as $seat) {
                    $gender = null;

                    if ($seat->gender) {
                        if ($seat->gender instanceof \App\Enums\GenderEnum) {
                            $gender = $seat->gender->value;
                        } elseif (is_string($seat->gender)) {
                            $gender = $seat->gender;
                        }
                    }

                    if (! $gender && $booking->passengers->isNotEmpty()) {
                        $passenger = $booking->passengers->first();
                        if ($passenger && $passenger->gender) {
                            if ($passenger->gender instanceof \App\Enums\GenderEnum) {
                                $gender = $passenger->gender->value;
                            } elseif (is_string($passenger->gender)) {
                                $gender = $passenger->gender;
                            }
                        }
                    }

                    $bookedSeats[$seat->seat_number] = [
                        'status' => 'booked',
                        'gender' => $gender,
                    ];
                }
            }
        }

        return $bookedSeats;
    }
}
