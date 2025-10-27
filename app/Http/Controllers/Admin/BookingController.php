<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\Fare;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\Terminal;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $routes = Route::where('status', 'active')->get();
        $terminals = Terminal::where('status', 'active')->get();

        return view('admin.bookings.create', compact('routes', 'terminals'));
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
                'status' => 'planned',
                'bus_id' => null, // Bus will be assigned later by admin
            ]
        );

        // Calculate fare
        $fare = $this->calculateSegmentFare($fromStop, $toStop, $route);

        // Get booked seats for this trip segment
        $bookedSeats = $this->getBookedSeatsForSegment($trip->id, $fromStop->id, $toStop->id);

        return view('admin.bookings.select-seats', compact('trip', 'fromStop', 'toStop', 'fare', 'bookedSeats'));
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
     * Helper: Get booked seats for a trip segment
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

        foreach ($bookings as $booking) {
            // Check if segments overlap
            // Overlap occurs if: (booking_from < search_to) AND (booking_to > search_from)
            if ($booking->fromStop->sequence < $toStop->sequence &&
                $booking->toStop->sequence > $fromStop->sequence) {
                $bookedSeats = array_merge(
                    $bookedSeats,
                    $booking->bookingSeats->pluck('seat_number')->toArray()
                );
            }
        }

        return array_unique($bookedSeats);
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
