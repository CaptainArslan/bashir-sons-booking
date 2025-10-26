<?php

namespace App\Services;

use App\Enums\TripStatusEnum;
use App\Events\TripBusAssigned;
use App\Models\Route;
use App\Models\Timetable;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TripService
{
    /**
     * Create or get trip for a given route and date
     */
    public function getOrCreateTrip(
        int $routeId,
        string $departureDate,
        ?int $timetableId = null
    ): Trip {
        $date = Carbon::parse($departureDate);

        // Try to find existing trip
        $trip = Trip::where('route_id', $routeId)
            ->whereDate('departure_date', $date)
            ->when($timetableId, function ($query) use ($timetableId) {
                $query->where('timetable_id', $timetableId);
            })
            ->first();

        if ($trip) {
            return $trip;
        }

        // Create new trip
        return $this->createTrip($routeId, $date, $timetableId);
    }

    /**
     * Create a new trip
     */
    public function createTrip(
        int $routeId,
        Carbon $departureDate,
        ?int $timetableId = null
    ): Trip {
        $route = Route::with('routeStops')->findOrFail($routeId);
        $timetable = $timetableId ? Timetable::find($timetableId) : null;

        // Calculate departure datetime
        $departureTime = $timetable?->start_departure_time ?? '00:00:00';
        $departureDatetime = Carbon::parse($departureDate->format('Y-m-d').' '.$departureTime);

        // Calculate estimated arrival
        $estimatedArrival = null;
        if ($timetable && $timetable->end_arrival_time) {
            $estimatedArrival = Carbon::parse($departureDate->format('Y-m-d').' '.$timetable->end_arrival_time);

            // Handle overnight trips
            if ($estimatedArrival < $departureDatetime) {
                $estimatedArrival->addDay();
            }
        }

        return Trip::create([
            'timetable_id' => $timetableId,
            'route_id' => $routeId,
            'bus_id' => null,
            'departure_date' => $departureDate,
            'departure_datetime' => $departureDatetime,
            'estimated_arrival_datetime' => $estimatedArrival,
            'status' => TripStatusEnum::Pending,
        ]);
    }

    /**
     * Assign bus to trip
     */
    public function assignBus(int $tripId, int $busId): bool
    {
        $trip = Trip::findOrFail($tripId);

        if (! $trip->canAssignBus()) {
            throw new \Exception('Cannot assign bus to trip in current status: '.$trip->status->value);
        }

        // Check if bus is already assigned to another trip at the same time
        $conflictingTrip = Trip::where('bus_id', $busId)
            ->where('id', '!=', $tripId)
            ->where('departure_datetime', $trip->departure_datetime)
            ->first();

        if ($conflictingTrip) {
            throw new \Exception('Bus is already assigned to another trip at this time.');
        }

        $trip->update([
            'bus_id' => $busId,
            'status' => TripStatusEnum::Scheduled,
        ]);

        event(new TripBusAssigned($trip));

        return true;
    }

    /**
     * Update trip status
     */
    public function updateStatus(int $tripId, TripStatusEnum $status): bool
    {
        $trip = Trip::findOrFail($tripId);

        return $trip->update(['status' => $status]);
    }

    /**
     * Start trip (mark as ongoing)
     */
    public function startTrip(int $tripId): bool
    {
        $trip = Trip::findOrFail($tripId);

        if (! $trip->hasBusAssigned()) {
            throw new \Exception('Cannot start trip without assigned bus.');
        }

        return $trip->update(['status' => TripStatusEnum::Ongoing]);
    }

    /**
     * Complete trip
     */
    public function completeTrip(int $tripId): bool
    {
        $trip = Trip::findOrFail($tripId);

        return $trip->update(['status' => TripStatusEnum::Completed]);
    }

    /**
     * Cancel trip
     */
    public function cancelTrip(int $tripId, string $reason): bool
    {
        $trip = Trip::findOrFail($tripId);

        // Cancel all pending/confirmed bookings
        $trip->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'notes' => 'Trip cancelled: '.$reason,
            ]);

        return $trip->update([
            'status' => TripStatusEnum::Cancelled,
            'notes' => $reason,
        ]);
    }

    /**
     * Get trips for a route
     */
    public function getTripsForRoute(
        int $routeId,
        ?string $startDate = null,
        ?string $endDate = null
    ): Collection {
        $query = Trip::with(['route', 'bus', 'timetable'])
            ->where('route_id', $routeId);

        if ($startDate) {
            $query->whereDate('departure_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('departure_date', '<=', $endDate);
        }

        return $query->orderBy('departure_datetime')->get();
    }

    /**
     * Get upcoming trips
     */
    public function getUpcomingTrips(int $limit = 10): Collection
    {
        return Trip::with(['route', 'bus', 'timetable'])
            ->upcoming()
            ->orderBy('departure_datetime')
            ->limit($limit)
            ->get();
    }

    /**
     * Get trip statistics
     */
    public function getTripStatistics(int $tripId): array
    {
        $trip = Trip::with([
            'bookings',
            'confirmedBookings',
            'expenses',
            'bus.busLayout',
        ])->findOrFail($tripId);

        $totalSeats = $trip->bus?->busLayout?->total_seats ?? 0;
        $bookedSeats = $trip->confirmedBookings()
            ->withCount('bookingSeats')
            ->get()
            ->sum('booking_seats_count');

        return [
            'trip_id' => $trip->id,
            'status' => $trip->status,
            'total_seats' => $totalSeats,
            'booked_seats' => $bookedSeats,
            'available_seats' => max(0, $totalSeats - $bookedSeats),
            'occupancy_rate' => $trip->getOccupancyRate(),
            'total_bookings' => $trip->bookings->count(),
            'confirmed_bookings' => $trip->confirmedBookings->count(),
            'total_revenue' => $trip->getTotalRevenue(),
            'total_expenses' => $trip->getTotalExpenses(),
            'net_profit' => $trip->getNetProfit(),
        ];
    }

    /**
     * Generate trips from timetables
     */
    public function generateTripsFromTimetables(
        string $startDate,
        string $endDate
    ): int {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $generated = 0;

        $timetables = Timetable::with('route')
            ->where('is_active', true)
            ->get();

        $currentDate = $start->copy();

        while ($currentDate <= $end) {
            foreach ($timetables as $timetable) {
                // Check if trip already exists
                $exists = Trip::where('timetable_id', $timetable->id)
                    ->whereDate('departure_date', $currentDate)
                    ->exists();

                if (! $exists) {
                    $this->createTrip(
                        $timetable->route_id,
                        $currentDate->copy(),
                        $timetable->id
                    );
                    $generated++;
                }
            }

            $currentDate->addDay();
        }

        return $generated;
    }

    /**
     * Delete trip if no bookings exist
     */
    public function deleteTrip(int $tripId): bool
    {
        $trip = Trip::findOrFail($tripId);

        if ($trip->bookings()->exists()) {
            throw new \Exception('Cannot delete trip with existing bookings.');
        }

        return $trip->delete();
    }

    /**
     * Get trips requiring bus assignment
     */
    public function getTripsRequiringBusAssignment(int $daysAhead = 7): Collection
    {
        return Trip::with(['route', 'timetable'])
            ->withoutBus()
            ->whereIn('status', [TripStatusEnum::Pending, TripStatusEnum::Scheduled])
            ->whereDate('departure_date', '>=', now())
            ->whereDate('departure_date', '<=', now()->addDays($daysAhead))
            ->orderBy('departure_datetime')
            ->get();
    }
}
