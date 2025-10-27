<?php

namespace App\Services;

use App\Enums\BookingStatusEnum;
use App\Enums\BookingTypeEnum;
use App\Enums\SeatLockTypeEnum;
use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\Fare;
use App\Models\RouteStop;
use App\Models\Trip;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function __construct(
        private SeatService $seatService,
        private TripService $tripService
    ) {}

    /**
     * Create a new booking
     */
    public function createBooking(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            // Get or create trip
            $trip = $this->tripService->getOrCreateTrip(
                $data['route_id'],
                $data['departure_date'],
                $data['timetable_id'] ?? null
            );

            if (! $trip->canAcceptBookings()) {
                throw new \Exception('Trip is not accepting bookings.');
            }

            // Calculate fare
            $fareDetails = $this->calculateFare(
                $data['from_stop_id'],
                $data['to_stop_id'],
                count($data['seats'])
            );

            // Create booking
            $booking = Booking::create([
                'trip_id' => $trip->id,
                'user_id' => $data['user_id'] ?? null,
                'booked_by_user_id' => auth()->id(),
                'from_stop_id' => $data['from_stop_id'],
                'to_stop_id' => $data['to_stop_id'],
                'type' => $data['type'] ?? BookingTypeEnum::Online,
                'status' => BookingStatusEnum::Pending,
                'total_fare' => $fareDetails['total_fare'],
                'discount_amount' => $data['discount_amount'] ?? 0,
                'final_amount' => $fareDetails['total_fare'] - ($data['discount_amount'] ?? 0),
                'currency' => $data['currency'] ?? 'PKR',
                'total_passengers' => count($data['seats']),
                'passenger_contact_phone' => $data['contact_phone'] ?? null,
                'passenger_contact_email' => $data['contact_email'] ?? null,
                'passenger_contact_name' => $data['contact_name'] ?? null,
                'notes' => $data['notes'] ?? null,
                'metadata' => $data['metadata'] ?? null,
            ]);

            // Create booking seats
            foreach ($data['seats'] as $seatData) {
                BookingSeat::create([
                    'booking_id' => $booking->id,
                    'seat_number' => $seatData['seat_number'],
                    'seat_row' => $seatData['seat_row'],
                    'seat_column' => $seatData['seat_column'],
                    'passenger_name' => $seatData['passenger_name'],
                    'passenger_age' => $seatData['passenger_age'] ?? null,
                    'passenger_gender' => $seatData['passenger_gender'] ?? null,
                    'passenger_cnic' => $seatData['passenger_cnic'] ?? null,
                    'passenger_phone' => $seatData['passenger_phone'] ?? null,
                    'fare' => $fareDetails['per_seat_fare'],
                    'notes' => $seatData['notes'] ?? null,
                ]);

                // Lock seat
                $this->seatService->lockSeat(
                    $trip->id,
                    $seatData['seat_number'],
                    $seatData,
                    $data['type'] == BookingTypeEnum::Phone->value
                        ? SeatLockTypeEnum::PhoneHold
                        : SeatLockTypeEnum::Temporary
                );
            }

            return $booking->load('bookingSeats', 'trip', 'fromStop', 'toStop');
        });
    }

    /**
     * Confirm a booking
     */
    public function confirmBooking(int|string $bookingId): Booking
    {
        return DB::transaction(function () use ($bookingId) {
            $booking = Booking::with('bookingSeats', 'trip')->findOrFail($bookingId);

            if (! $booking->isPending()) {
                throw new \Exception('Only pending bookings can be confirmed.');
            }

            $booking->confirm();

            // Release seat locks (they're now permanently booked)
            foreach ($booking->bookingSeats as $seat) {
                $this->seatService->releaseSeat(
                    $booking->trip_id,
                    $seat->seat_number
                );
            }

            return $booking->fresh();
        });
    }

    /**
     * Cancel a booking
     */
    public function cancelBooking(int|string $bookingId, ?string $reason = null): bool
    {
        return DB::transaction(function () use ($bookingId, $reason) {
            $booking = Booking::with('bookingSeats', 'trip')->findOrFail($bookingId);

            if (! $booking->canBeCancelled()) {
                throw new \Exception('This booking cannot be cancelled.');
            }

            // Release seat locks
            foreach ($booking->bookingSeats as $seat) {
                $this->seatService->releaseSeat(
                    $booking->trip_id,
                    $seat->seat_number
                );
            }

            return $booking->cancel($reason);
        });
    }

    /**
     * Calculate fare for a route segment
     */
    public function calculateFare(
        int $fromStopId,
        int $toStopId,
        int $passengerCount = 1
    ): array {
        $fromStop = RouteStop::findOrFail($fromStopId);
        $toStop = RouteStop::findOrFail($toStopId);

        if ($fromStop->route_id !== $toStop->route_id) {
            throw new \Exception('Stops must be on the same route.');
        }

        if ($fromStop->sequence >= $toStop->sequence) {
            throw new \Exception('From stop must come before to stop.');
        }

        // Get all stops between from and to
        $stops = RouteStop::where('route_id', $fromStop->route_id)
            ->whereBetween('sequence', [$fromStop->sequence, $toStop->sequence])
            ->orderBy('sequence')
            ->get();

        $totalFare = 0;

        // Calculate fare for each segment
        for ($i = 0; $i < $stops->count() - 1; $i++) {
            $fare = Fare::where('from_terminal_id', $stops[$i]->terminal_id)
                ->where('to_terminal_id', $stops[$i + 1]->terminal_id)
                ->where('status', 'active')
                ->first();

            if ($fare) {
                $totalFare += $fare->final_fare;
            }
        }

        return [
            'per_seat_fare' => $totalFare,
            'total_fare' => $totalFare * $passengerCount,
            'passenger_count' => $passengerCount,
            'currency' => 'PKR',
        ];
    }

    /**
     * Search available trips
     */
    public function searchTrips(array $criteria): array
    {
        $trips = Trip::with(['route', 'bus.busLayout', 'timetable'])
            ->where('route_id', $criteria['route_id'])
            ->whereDate('departure_date', $criteria['date'])
            ->whereIn('status', [
                TripStatusEnum::Pending,
                TripStatusEnum::Scheduled,
                TripStatusEnum::Boarding,
            ])
            ->get();

        $results = [];

        foreach ($trips as $trip) {
            $availableSeats = $this->seatService->getAvailableSeats(
                $trip->id,
                $criteria['from_stop_id'],
                $criteria['to_stop_id']
            );

            if (count($availableSeats) >= ($criteria['passengers'] ?? 1)) {
                $fareDetails = $this->calculateFare(
                    $criteria['from_stop_id'],
                    $criteria['to_stop_id'],
                    $criteria['passengers'] ?? 1
                );

                $results[] = [
                    'trip' => $trip,
                    'available_seats' => count($availableSeats),
                    'seats' => $availableSeats,
                    'fare' => $fareDetails,
                ];
            }
        }

        return $results;
    }

    /**
     * Get booking details
     */
    public function getBookingDetails(int|string $bookingId): Booking
    {
        return Booking::with([
            'trip.route.routeStops.terminal',
            'trip.bus.busLayout',
            'bookingSeats',
            'user',
            'bookedByUser',
            'fromStop.terminal',
            'toStop.terminal',
        ])->findOrFail($bookingId);
    }

    /**
     * Get user bookings
     */
    public function getUserBookings(int $userId, ?string $status = null)
    {
        $query = Booking::with([
            'trip.route',
            'bookingSeats',
            'fromStop.terminal',
            'toStop.terminal',
        ])
            ->where('user_id', $userId)
            ->orWhere('booked_by_user_id', $userId);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Release phone bookings before departure
     */
    public function releasePhoneBookingsBeforeDeparture(int $minutesBefore = 30): int
    {
        $trips = Trip::where('departure_datetime', '>', now())
            ->where('departure_datetime', '<=', now()->addMinutes($minutesBefore))
            ->with('bookings')
            ->get();

        $releasedCount = 0;

        foreach ($trips as $trip) {
            $phoneBookings = $trip->bookings()
                ->where('type', BookingTypeEnum::Phone)
                ->where('status', BookingStatusEnum::Pending)
                ->get();

            foreach ($phoneBookings as $booking) {
                $this->cancelBooking(
                    $booking->id,
                    'Automatically cancelled - phone booking not confirmed before departure'
                );
                $releasedCount++;
            }
        }

        return $releasedCount;
    }

    /**
     * Validate employee booking permissions
     */
    public function validateEmployeeBooking(int $userId, int $routeId, int $fromStopId): bool
    {
        $user = \App\Models\User::with(['terminal', 'employeeRoutes'])->findOrFail($userId);

        if (! $user->isEmployee()) {
            throw new \Exception('User is not an employee.');
        }

        // Check if employee has permission for this route
        $hasRoutePermission = $user->employeeRoutes()
            ->where('route_id', $routeId)
            ->where('can_book', true)
            ->exists();

        if (! $hasRoutePermission) {
            throw new \Exception('Employee does not have permission to book for this route.');
        }

        // Check if booking starts from or after employee's terminal
        $fromStop = RouteStop::with('route.routeStops')->findOrFail($fromStopId);

        $employeeTerminalStop = $fromStop->route->routeStops()
            ->where('terminal_id', $user->terminal_id)
            ->first();

        if (! $employeeTerminalStop) {
            throw new \Exception('Employee terminal is not on this route.');
        }

        $fromStopSequence = $fromStop->sequence;
        $employeeStopSequence = $employeeTerminalStop->sequence;

        if ($fromStopSequence < $employeeStopSequence) {
            throw new \Exception('Employee can only book from or after their assigned terminal.');
        }

        return true;
    }
}
