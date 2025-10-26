<?php

namespace App\Services;

use App\Enums\TripStatusEnum;
use App\Models\Trip;
use Illuminate\Support\Collection;

class TripLifecycleService
{
    public function __construct(
        private TripService $tripService
    ) {}

    /**
     * Process trips that should be boarding
     * (e.g., 30 minutes before departure)
     */
    public function processBoardingTrips(int $minutesBefore = 30): int
    {
        $trips = Trip::where('status', TripStatusEnum::Scheduled)
            ->where('departure_datetime', '>', now())
            ->where('departure_datetime', '<=', now()->addMinutes($minutesBefore))
            ->get();

        $count = 0;

        foreach ($trips as $trip) {
            $this->tripService->updateStatus($trip->id, TripStatusEnum::Boarding);
            $count++;
        }

        return $count;
    }

    /**
     * Process trips that should start
     */
    public function processStartingTrips(): int
    {
        $trips = Trip::whereIn('status', [TripStatusEnum::Scheduled, TripStatusEnum::Boarding])
            ->where('departure_datetime', '<=', now())
            ->where('departure_datetime', '>=', now()->subMinutes(15))
            ->get();

        $count = 0;

        foreach ($trips as $trip) {
            if ($trip->hasBusAssigned()) {
                $this->tripService->startTrip($trip->id);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Process trips that should be completed
     */
    public function processCompletedTrips(): int
    {
        $trips = Trip::where('status', TripStatusEnum::Ongoing)
            ->whereNotNull('estimated_arrival_datetime')
            ->where('estimated_arrival_datetime', '<=', now())
            ->get();

        $count = 0;

        foreach ($trips as $trip) {
            $this->tripService->completeTrip($trip->id);
            $count++;
        }

        return $count;
    }

    /**
     * Process delayed trips
     */
    public function processDelayedTrips(): int
    {
        $trips = Trip::whereIn('status', [TripStatusEnum::Scheduled, TripStatusEnum::Boarding])
            ->where('departure_datetime', '<', now()->subMinutes(15))
            ->get();

        $count = 0;

        foreach ($trips as $trip) {
            $this->tripService->updateStatus($trip->id, TripStatusEnum::Delayed);
            $count++;
        }

        return $count;
    }

    /**
     * Get trips requiring attention
     */
    public function getTripsRequiringAttention(): array
    {
        return [
            'no_bus_assigned' => $this->getTripsWithoutBus(),
            'boarding_soon' => $this->getTripsBoardingSoon(),
            'delayed' => $this->getDelayedTrips(),
            'low_occupancy' => $this->getLowOccupancyTrips(),
        ];
    }

    /**
     * Get trips without bus assigned
     */
    private function getTripsWithoutBus(): Collection
    {
        return Trip::withoutBus()
            ->whereIn('status', [TripStatusEnum::Pending])
            ->where('departure_datetime', '>', now())
            ->where('departure_datetime', '<=', now()->addDays(3))
            ->with(['route', 'timetable'])
            ->orderBy('departure_datetime')
            ->get();
    }

    /**
     * Get trips boarding soon
     */
    private function getTripsBoardingSoon(int $minutes = 60): Collection
    {
        return Trip::whereIn('status', [TripStatusEnum::Scheduled])
            ->where('departure_datetime', '>', now())
            ->where('departure_datetime', '<=', now()->addMinutes($minutes))
            ->with(['route', 'bus', 'bookings'])
            ->orderBy('departure_datetime')
            ->get();
    }

    /**
     * Get delayed trips
     */
    private function getDelayedTrips(): Collection
    {
        return Trip::where('status', TripStatusEnum::Delayed)
            ->where('departure_datetime', '>=', now()->subHours(24))
            ->with(['route', 'bus'])
            ->orderBy('departure_datetime')
            ->get();
    }

    /**
     * Get low occupancy trips
     */
    private function getLowOccupancyTrips(float $threshold = 30.0): Collection
    {
        return Trip::whereIn('status', [
            TripStatusEnum::Scheduled,
            TripStatusEnum::Boarding,
        ])
            ->where('departure_datetime', '>', now())
            ->whereHas('bus.busLayout')
            ->with(['route', 'bus.busLayout', 'confirmedBookings'])
            ->get()
            ->filter(function ($trip) use ($threshold) {
                return $trip->getOccupancyRate() < $threshold;
            });
    }

    /**
     * Auto-cancel trips with no bookings
     */
    public function autoCancelEmptyTrips(int $hoursBefore = 6): int
    {
        $trips = Trip::whereIn('status', [TripStatusEnum::Pending, TripStatusEnum::Scheduled])
            ->where('departure_datetime', '>', now())
            ->where('departure_datetime', '<=', now()->addHours($hoursBefore))
            ->doesntHave('confirmedBookings')
            ->get();

        $count = 0;

        foreach ($trips as $trip) {
            $this->tripService->cancelTrip(
                $trip->id,
                'Auto-cancelled: No bookings'
            );
            $count++;
        }

        return $count;
    }

    /**
     * Get trip lifecycle statistics
     */
    public function getLifecycleStatistics(?string $date = null): array
    {
        $query = Trip::query();

        if ($date) {
            $query->whereDate('departure_date', $date);
        } else {
            $query->whereDate('departure_date', now());
        }

        $trips = $query->get();

        return [
            'date' => $date ?? now()->format('Y-m-d'),
            'total_trips' => $trips->count(),
            'by_status' => [
                'pending' => $trips->where('status', TripStatusEnum::Pending)->count(),
                'scheduled' => $trips->where('status', TripStatusEnum::Scheduled)->count(),
                'boarding' => $trips->where('status', TripStatusEnum::Boarding)->count(),
                'ongoing' => $trips->where('status', TripStatusEnum::Ongoing)->count(),
                'completed' => $trips->where('status', TripStatusEnum::Completed)->count(),
                'cancelled' => $trips->where('status', TripStatusEnum::Cancelled)->count(),
                'delayed' => $trips->where('status', TripStatusEnum::Delayed)->count(),
            ],
            'with_bus' => $trips->whereNotNull('bus_id')->count(),
            'without_bus' => $trips->whereNull('bus_id')->count(),
            'with_bookings' => $trips->filter(fn ($t) => $t->bookings()->exists())->count(),
            'without_bookings' => $trips->filter(fn ($t) => ! $t->bookings()->exists())->count(),
        ];
    }

    /**
     * Process all automatic trip lifecycle updates
     */
    public function processAllAutomaticUpdates(): array
    {
        return [
            'boarding_trips' => $this->processBoardingTrips(),
            'starting_trips' => $this->processStartingTrips(),
            'completed_trips' => $this->processCompletedTrips(),
            'delayed_trips' => $this->processDelayedTrips(),
        ];
    }
}
