<?php

namespace App\Http\Controllers\Admin;

use App\Models\Fare;
use App\Models\Trip;
use App\Models\Route;
use App\Models\Booking;
use App\Models\Terminal;
use App\Models\RouteStop;
use App\Models\Timetable;
use App\Events\SeatLocked;
use App\Models\BookingSeat;
use App\Events\SeatReleased;
use Illuminate\Http\Request;
use App\Enums\BookingStatusEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings
     */
    public function index()
    {
        $bookings = Booking::with(['trip.route', 'trip.bus', 'fromStop.terminal', 'toStop.terminal', 'user', 'bookingSeats'])
            ->latest()
            ->paginate(20);

        return view('admin.bookings.index', compact('bookings'));
    }

    /**
     * Show the search form for creating a new booking
     */
    public function create()
    {
        $terminals = Terminal::where('status', 'active')->get();

        return view('admin.bookings.create', compact('terminals'));
    }

    /**
     * Get available departure times based on terminals
     */
    public function getAvailableTimes(Request $request)
    {
        $validated = $request->validate([
            'from_terminal_id' => 'required|exists:terminals,id',
            'to_terminal_id' => 'required|exists:terminals,id|different:from_terminal_id',
            'departure_date' => 'required|date|after_or_equal:today',
        ]);

        // Find routes that connect these two terminals
        $routes = Route::where('status', 'active')
            ->whereHas('routeStops', function ($query) use ($validated) {
                $query->where('terminal_id', $validated['from_terminal_id']);
            })
            ->whereHas('routeStops', function ($query) use ($validated) {
                $query->where('terminal_id', $validated['to_terminal_id']);
            })
            ->with(['routeStops' => function ($query) {
                $query->orderBy('sequence');
            }])
            ->get();

        if ($routes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No routes found connecting these terminals.',
            ]);
        }

        // Get available times from timetables
        $availableTimes = [];
        $now = now();

        foreach ($routes as $route) {
            // Validate that to_terminal comes after from_terminal in sequence
            $fromStop = $route->routeStops->where('terminal_id', $validated['from_terminal_id'])->first();
            $toStop = $route->routeStops->where('terminal_id', $validated['to_terminal_id'])->first();

            if (! $fromStop || ! $toStop || $toStop->sequence <= $fromStop->sequence) {
                continue; // Skip invalid route configurations
            }

            // Get timetables for this route
            $timetables = Timetable::where('route_id', $route->id)
                ->where('is_active', true)
                ->with(['timetableStops' => function ($query) use ($validated) {
                    $query->where('terminal_id', $validated['from_terminal_id'])
                        ->orderBy('sequence');
                }])
                ->get();

            foreach ($timetables as $timetable) {
                $timetableStop = $timetable->timetableStops->first();
                if ($timetableStop && $timetableStop->departure_time) {
                    $departureDateTime = now()->setTimeFromTimeString($timetableStop->departure_time);
                    
                    // Only include times that are in the future
                    if ($departureDateTime->isAfter($now)) {
                        $availableTimes[] = [
                            'time' => $departureDateTime->format('H:i A'),
                            'route_id' => $route->id,
                            'route_name' => $route->name,
                            'route_code' => $route->code,
                        ];
                    }
                }
            }
        }

        // If no timetables found, return the first valid route for manual time entry
        if (empty($availableTimes)) {
            $firstRoute = $routes->first(function ($route) use ($validated) {
                $fromStop = $route->routeStops->where('terminal_id', $validated['from_terminal_id'])->first();
                $toStop = $route->routeStops->where('terminal_id', $validated['to_terminal_id'])->first();

                return $fromStop && $toStop && $toStop->sequence > $fromStop->sequence;
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'times' => [],
                    'route_id' => $firstRoute ? $firstRoute->id : null,
                ],
            ]);
        }

        // Sort times chronologically
        usort($availableTimes, function ($a, $b) {
            return strcmp($a['time'], $b['time']);
        });

        return response()->json([
            'success' => true,
            'data' => [
                'times' => $availableTimes,
            ],
        ]);
    }

    /**
     * Search for available seats
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'route_id' => 'required|exists:routes,id',
            'from_terminal_id' => 'required|exists:terminals,id',
            'to_terminal_id' => 'required|exists:terminals,id',
            'departure_date' => 'required|date|after_or_equal:today',
            'departure_time' => 'required',
        ]);

        // Get route stops for the selected terminals
        $route = Route::with('routeStops.terminal')->findOrFail($validated['route_id']);

        $fromStop = $route->routeStops()
            ->where('terminal_id', $validated['from_terminal_id'])
            ->first();

        $toStop = $route->routeStops()
            ->where('terminal_id', $validated['to_terminal_id'])
            ->first();

        if (! $fromStop || ! $toStop) {
            return back()->with('error', 'Selected terminals are not part of this route.');
        }

        // Validate sequence (to_stop must come after from_stop)
        if ($toStop->sequence <= $fromStop->sequence) {
            return back()->with('error', 'Destination must come after starting point in the route.');
        }

        // Create departure datetime
        $departureDateTime = $validated['departure_date'].' '.$validated['departure_time'];

        // Check if trip exists, if not create one
        $trip = Trip::firstOrCreate(
            [
                'route_id' => $validated['route_id'],
                'departure_date' => $validated['departure_date'],
            ],
            [
                'departure_datetime' => $departureDateTime,
                'estimated_arrival_datetime' => date('Y-m-d H:i:s', strtotime($departureDateTime.' +4 hours')),
                'status' => 'pending',
                'bus_id' => null, // Bus will be assigned later by admin
            ]
        );

        // Calculate fare
        $fare = $this->calculateSegmentFare($fromStop, $toStop, $route);

        // Get booked seats and their statuses for this trip segment
        $bookedSeats = $this->getBookedSeatsForSegment($trip->id, $fromStop->id, $toStop->id);
        $seatStatuses = $this->getSeatStatusesForSegment($trip->id, $fromStop->id, $toStop->id);

        return view('admin.bookings.select-seats', compact('trip', 'fromStop', 'toStop', 'fare', 'bookedSeats', 'seatStatuses'));
    }

    /**
     * Show seat selection and passenger details form
     */
    public function selectSeats(Request $request)
    {
        $validated = $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'from_stop_id' => 'required|exists:route_stops,id',
            'to_stop_id' => 'required|exists:route_stops,id',
            'selected_seats' => 'required|array|min:1',
            'selected_seats.*' => 'required|string',
        ]);

        $trip = Trip::with(['route', 'bus'])->findOrFail($validated['trip_id']);
        $fromStop = RouteStop::with('terminal')->findOrFail($validated['from_stop_id']);
        $toStop = RouteStop::with('terminal')->findOrFail($validated['to_stop_id']);

        // Calculate fare per seat
        $farePerSeat = $this->calculateSegmentFare($fromStop, $toStop, $trip->route);

        // Check if selected seats are available
        $bookedSeats = $this->getBookedSeatsForSegment($trip->id, $fromStop->id, $toStop->id);
        $unavailableSeats = array_intersect($validated['selected_seats'], $bookedSeats);

        if (count($unavailableSeats) > 0) {
            return back()->with('error', 'Some selected seats are no longer available: '.implode(', ', $unavailableSeats));
        }

        return view('admin.bookings.passenger-details', [
            'trip' => $trip,
            'fromStop' => $fromStop,
            'toStop' => $toStop,
            'selectedSeats' => $validated['selected_seats'],
            'farePerSeat' => $farePerSeat,
            'totalFare' => $farePerSeat * count($validated['selected_seats']),
        ]);
    }

    /**
     * Store a newly created booking
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'from_stop_id' => 'required|exists:route_stops,id',
            'to_stop_id' => 'required|exists:route_stops,id',
            'booking_type' => 'required|in:counter,phone',
            'payment_method' => 'required|in:cash,card,mobile_wallet,bank_transfer,other',
            'payment_status' => 'required|in:pending,paid',
            'total_fare' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'passenger_contact_name' => 'required|string|max:255',
            'passenger_contact_phone' => 'required|string|max:20',
            'passenger_contact_email' => 'nullable|email',
            'notes' => 'nullable|string',
            'seats' => 'required|array|min:1',
            'seats.*.seat_number' => 'required|string',
            'seats.*.passenger_name' => 'required|string|max:255',
            'seats.*.passenger_cnic' => 'required|string|max:20',
            'seats.*.passenger_phone' => 'nullable|string|max:20',
            'seats.*.passenger_age' => 'nullable|integer|min:1|max:150',
            'seats.*.passenger_gender' => 'required|in:male,female,other',
            'seats.*.fare' => 'required|numeric|min:0',
        ], [
            'seats.*.passenger_name.required' => 'Passenger name is required for all seats.',
            'seats.*.passenger_cnic.required' => 'Passenger CNIC is required for all seats.',
            'seats.*.passenger_gender.required' => 'Passenger gender is required for all seats.',
        ]);

        try {
            DB::beginTransaction();

            // Check if seats are still available
            $seatNumbers = array_column($validated['seats'], 'seat_number');
            $bookedSeats = $this->getBookedSeatsForSegment(
                $validated['trip_id'],
                $validated['from_stop_id'],
                $validated['to_stop_id']
            );

            $unavailableSeats = array_intersect($seatNumbers, $bookedSeats);
            if (count($unavailableSeats) > 0) {
                return back()->with('error', 'Some seats are no longer available: '.implode(', ', $unavailableSeats));
            }

            // Calculate final amount
            $discountAmount = $validated['discount_amount'] ?? 0;
            $finalAmount = $validated['total_fare'] - $discountAmount;

            // Create booking
            $booking = Booking::create([
                'trip_id' => $validated['trip_id'],
                'user_id' => null, // No customer user for counter/phone bookings
                'booked_by_user_id' => auth()->id(),
                'from_stop_id' => $validated['from_stop_id'],
                'to_stop_id' => $validated['to_stop_id'],
                'type' => $validated['booking_type'],
                'status' => $validated['payment_status'] === 'paid' ? BookingStatusEnum::Confirmed : BookingStatusEnum::Pending,
                'total_fare' => $validated['total_fare'],
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'currency' => 'PKR',
                'total_passengers' => count($validated['seats']),
                'passenger_contact_name' => $validated['passenger_contact_name'],
                'passenger_contact_phone' => $validated['passenger_contact_phone'],
                'passenger_contact_email' => $validated['passenger_contact_email'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'payment_status' => $validated['payment_status'],
                'payment_method' => $validated['payment_method'],
                'confirmed_at' => $validated['payment_status'] === 'paid' ? now() : null,
                'reserved_until' => $validated['booking_type'] === 'phone' ? $this->calculateReservedUntil($validated['trip_id']) : null,
            ]);

            // Create booking seats
            foreach ($validated['seats'] as $seatData) {
                BookingSeat::create([
                    'booking_id' => $booking->id,
                    'seat_number' => $seatData['seat_number'],
                    'passenger_name' => $seatData['passenger_name'],
                    'passenger_cnic' => $seatData['passenger_cnic'],
                    'passenger_phone' => $seatData['passenger_phone'] ?? null,
                    'passenger_age' => $seatData['passenger_age'] ?? null,
                    'passenger_gender' => $seatData['passenger_gender'],
                    'fare' => $seatData['fare'],
                ]);
            }

            DB::commit();

            return redirect()->route('admin.bookings.show', $booking->id)
                ->with('success', 'Booking created successfully! Booking Number: '.$booking->booking_number);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking creation failed: '.$e->getMessage());

            return back()->with('error', 'Failed to create booking. Please try again.');
        }
    }

    /**
     * Display the specified booking
     */
    public function show(string $id)
    {
        $booking = Booking::with([
            'trip.route.routeStops.terminal',
            'trip.bus',
            'fromStop.terminal',
            'toStop.terminal',
            'bookingSeats',
            'bookedByUser',
        ])->findOrFail($id);

        return view('admin.bookings.show', compact('booking'));
    }

    /**
     * Cancel a booking
     */
    public function cancel(Request $request, string $id)
    {
        $validated = $request->validate([
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        $booking = Booking::findOrFail($id);

        if ($booking->isCancelled()) {
            return back()->with('error', 'Booking is already cancelled.');
        }

        if ($booking->cancel($validated['cancellation_reason'] ?? null)) {
            return redirect()->route('admin.bookings.show', $booking->id)
                ->with('success', 'Booking cancelled successfully.');
        }

        return back()->with('error', 'Unable to cancel booking.');
    }

    /**
     * Lock a seat temporarily using Cache (works with Pusher/Ably)
     */
    public function lockSeat(Request $request)
    {
        $validated = $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'from_stop_id' => 'required|exists:route_stops,id',
            'to_stop_id' => 'required|exists:route_stops,id',
            'seat_number' => 'required|string',
            'gender' => 'required|in:male,female',
        ]);

        $lockKey = "seat_lock:{$validated['trip_id']}:{$validated['seat_number']}:{$validated['from_stop_id']}:{$validated['to_stop_id']}";
        $lockValue = [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'session_id' => session()->getId(),
            'gender' => $validated['gender'],
            'locked_at' => now()->toDateTimeString(),
        ];

        // Check if seat is already locked by another user
        $existingLock = Cache::get($lockKey);
        if ($existingLock && $existingLock['session_id'] !== session()->getId()) {
            return response()->json([
                'success' => false,
                'message' => 'This seat has been selected by another user.',
            ]);
        }

        // Check if seat is already booked
        $isBooked = $this->isSeatBooked($validated['trip_id'], $validated['seat_number'], $validated['from_stop_id'], $validated['to_stop_id']);
        if ($isBooked) {
            return response()->json([
                'success' => false,
                'message' => 'This seat is already booked.',
            ]);
        }

        // Lock seat for 10 minutes
        Cache::put($lockKey, $lockValue, now()->addMinutes(10));

        // Broadcast seat locked event
        broadcast(new SeatLocked(
            $validated['trip_id'],
            $validated['seat_number'],
            $validated['from_stop_id'],
            $validated['to_stop_id'],
            $validated['gender'],
            auth()->user()->name,
            session()->getId()
        ))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Seat locked successfully.',
        ]);
    }

    /**
     * Unlock a seat
     */
    public function unlockSeat(Request $request)
    {
        $validated = $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'from_stop_id' => 'required|exists:route_stops,id',
            'to_stop_id' => 'required|exists:route_stops,id',
            'seat_number' => 'required|string',
        ]);

        $lockKey = "seat_lock:{$validated['trip_id']}:{$validated['seat_number']}:{$validated['from_stop_id']}:{$validated['to_stop_id']}";

        // Only allow unlock if it's the same session
        $existingLock = Cache::get($lockKey);
        if ($existingLock && $existingLock['session_id'] === session()->getId()) {
            Cache::forget($lockKey);

            // Broadcast seat released event
            broadcast(new SeatReleased(
                $validated['trip_id'],
                $validated['seat_number'],
                $validated['from_stop_id'],
                $validated['to_stop_id'],
                session()->getId()
            ))->toOthers();
        }

        return response()->json([
            'success' => true,
            'message' => 'Seat unlocked successfully.',
        ]);
    }

    /**
     * Check seat availability and statuses
     */
    public function checkSeats(Request $request)
    {
        $validated = $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'from_stop_id' => 'required|exists:route_stops,id',
            'to_stop_id' => 'required|exists:route_stops,id',
        ]);

        // Get locked seats from Cache with gender info
        $lockedSeats = [];
        for ($seatNumber = 1; $seatNumber <= 45; $seatNumber++) {
            $lockKey = "seat_lock:{$validated['trip_id']}:{$seatNumber}:{$validated['from_stop_id']}:{$validated['to_stop_id']}";
            $lockData = Cache::get($lockKey);

            // Only include seats locked by OTHER users
            if ($lockData && $lockData['session_id'] !== session()->getId()) {
                $lockedSeats[$seatNumber] = [
                    'gender' => $lockData['gender'],
                    'user_name' => $lockData['user_name'],
                    'locked_at' => $lockData['locked_at'],
                ];
            }
        }

        // Get seat statuses from database (booked vs confirmed)
        $seatStatuses = $this->getSeatStatusesForSegment($validated['trip_id'], $validated['from_stop_id'], $validated['to_stop_id']);

        return response()->json([
            'success' => true,
            'data' => [
                'locked_seats' => $lockedSeats,
                'seat_statuses' => $seatStatuses,
            ],
        ]);
    }

    /**
     * Helper: Check if a specific seat is booked
     */
    protected function isSeatBooked(int $tripId, string $seatNumber, int $fromStopId, int $toStopId): bool
    {
        $bookedSeats = $this->getBookedSeatsForSegment($tripId, $fromStopId, $toStopId);

        return in_array($seatNumber, $bookedSeats);
    }

    /**
     * Helper: Calculate fare between two stops
     */
    protected function calculateSegmentFare(RouteStop $fromStop, RouteStop $toStop, Route $route): float
    {
        $totalFare = 0;

        // Get all stops between from and to
        $stops = $route->routeStops()
            ->whereBetween('sequence', [$fromStop->sequence, $toStop->sequence])
            ->orderBy('sequence')
            ->get();

        // Sum up fares for consecutive stop pairs
        for ($i = 0; $i < count($stops) - 1; $i++) {
            $fare = Fare::where('from_terminal_id', $stops[$i]->terminal_id)
                ->where('to_terminal_id', $stops[$i + 1]->terminal_id)
                ->value('final_fare');

            $totalFare += $fare ?? 0;
        }

        return $totalFare;
    }

    /**
     * Helper: Get booked seats for a trip segment with status
     */
    protected function getBookedSeatsForSegment(int $tripId, int $fromStopId, int $toStopId): array
    {
        // Get the sequences for comparison
        $fromStop = RouteStop::findOrFail($fromStopId);
        $toStop = RouteStop::findOrFail($toStopId);

        // Get all bookings that overlap with this segment
        $bookings = Booking::where('trip_id', $tripId)
            ->whereIn('status', [BookingStatusEnum::Pending, BookingStatusEnum::Confirmed])
            ->with(['fromStop', 'toStop', 'bookingSeats'])
            ->get();

        $bookedSeats = [];
        $seatStatuses = [];

        foreach ($bookings as $booking) {
            // Check if segments overlap
            // Overlap occurs if: (booking_from < search_to) AND (booking_to > search_from)
            if ($booking->fromStop->sequence < $toStop->sequence &&
                $booking->toStop->sequence > $fromStop->sequence) {
                foreach ($booking->bookingSeats as $seat) {
                    $bookedSeats[] = $seat->seat_number;
                    $seatStatuses[$seat->seat_number] = [
                        'status' => $booking->status->value,
                        'is_confirmed' => $booking->status === BookingStatusEnum::Confirmed,
                    ];
                }
            }
        }

        return array_unique($bookedSeats);
    }

    /**
     * Helper: Get seat statuses (booked vs confirmed) for a trip segment
     */
    protected function getSeatStatusesForSegment(int $tripId, int $fromStopId, int $toStopId): array
    {
        $fromStop = RouteStop::findOrFail($fromStopId);
        $toStop = RouteStop::findOrFail($toStopId);

        $bookings = Booking::where('trip_id', $tripId)
            ->whereIn('status', [BookingStatusEnum::Pending, BookingStatusEnum::Confirmed])
            ->with(['fromStop', 'toStop', 'bookingSeats'])
            ->get();

        $seatStatuses = [];

        foreach ($bookings as $booking) {
            if ($booking->fromStop->sequence < $toStop->sequence &&
                $booking->toStop->sequence > $fromStop->sequence) {
                foreach ($booking->bookingSeats as $seat) {
                    $seatStatuses[$seat->seat_number] = [
                        'status' => $booking->status->value,
                        'is_confirmed' => $booking->status === BookingStatusEnum::Confirmed,
                        'booking_number' => $booking->booking_number,
                    ];
                }
            }
        }

        return $seatStatuses;
    }

    /**
     * Helper: Calculate reserved_until timestamp for phone bookings
     */
    protected function calculateReservedUntil(int $tripId): string
    {
        $trip = Trip::findOrFail($tripId);

        // Reserve until 30 minutes before departure
        return date('Y-m-d H:i:s', strtotime($trip->departure_datetime.' -30 minutes'));
    }
}
