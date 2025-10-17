<?php

namespace App\Services;

use App\Models\Route;
use App\Models\RouteTimetable;
use App\Models\RouteStopTime;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RouteTimetableService
{
    /**
     * Get available timetables for booking on a specific date.
     */
    public function getAvailableTimetablesForDate(Route $route, Carbon $date): Collection
    {
        return $route->activeTimetables()
            ->with(['stopsOrdered.routeStop.terminal'])
            ->get()
            ->filter(function (RouteTimetable $timetable) use ($date) {
                return $timetable->operatesOn($date->format('l')) && 
                       $timetable->getNextDepartureTime($date) !== null;
            });
    }

    /**
     * Get available booking stops for a timetable on a specific date.
     */
    public function getAvailableBookingStops(RouteTimetable $timetable, Carbon $date): Collection
    {
        return $timetable->stopsOrdered()
            ->where('allow_online_booking', true)
            ->with('routeStop.terminal')
            ->get()
            ->filter(function (RouteStopTime $stopTime) use ($date) {
                return $stopTime->isAvailableForBooking($date);
            });
    }

    /**
     * Get available trips for booking between two stops on a specific date.
     */
    public function getAvailableTrips(
        Route $route, 
        int $fromStopId, 
        int $toStopId, 
        Carbon $date
    ): Collection {
        $timetables = $this->getAvailableTimetablesForDate($route, $date);
        
        return $timetables->map(function (RouteTimetable $timetable) use ($fromStopId, $toStopId, $date) {
            $fromStop = $timetable->stopsOrdered()
                ->where('route_stop_id', $fromStopId)
                ->first();
                
            $toStop = $timetable->stopsOrdered()
                ->where('route_stop_id', $toStopId)
                ->first();

            if (!$fromStop || !$toStop || $fromStop->sequence >= $toStop->sequence) {
                return null;
            }

            if (!$fromStop->isAvailableForBooking($date)) {
                return null;
            }

            return [
                'timetable' => $timetable,
                'from_stop' => $fromStop,
                'to_stop' => $toStop,
                'departure_time' => $fromStop->getDepartureTimeForDate($date),
                'arrival_time' => $toStop->getArrivalTimeForDate($date),
                'duration' => $this->calculateDuration($fromStop, $toStop),
            ];
        })->filter()->values();
    }

    /**
     * Calculate travel duration between two stops.
     */
    private function calculateDuration(RouteStopTime $fromStop, RouteStopTime $toStop): int
    {
        $fromTime = $fromStop->getDepartureTimeForDate(Carbon::today());
        $toTime = $toStop->getArrivalTimeForDate(Carbon::today());

        if (!$fromTime || !$toTime) {
            return 0;
        }

        return $fromTime->diffInMinutes($toTime);
    }

    /**
     * Check if a trip is available for booking.
     */
    public function isTripAvailableForBooking(
        RouteTimetable $timetable, 
        int $fromStopId, 
        int $toStopId, 
        Carbon $date
    ): bool {
        $fromStop = $timetable->stopsOrdered()
            ->where('route_stop_id', $fromStopId)
            ->first();
            
        $toStop = $timetable->stopsOrdered()
            ->where('route_stop_id', $toStopId)
            ->first();

        if (!$fromStop || !$toStop) {
            return false;
        }

        if ($fromStop->sequence >= $toStop->sequence) {
            return false;
        }

        if (!$fromStop->allow_online_booking || !$toStop->allow_online_booking) {
            return false;
        }

        if (!$timetable->operatesOn($date->format('l'))) {
            return false;
        }

        $departureTime = $fromStop->getDepartureTimeForDate($date);
        
        return $departureTime && $departureTime->isFuture();
    }

    /**
     * Get timetable schedule for a specific date range.
     */
    public function getTimetableSchedule(RouteTimetable $timetable, Carbon $startDate, Carbon $endDate): Collection
    {
        $schedule = collect();
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            if ($timetable->operatesOn($currentDate->format('l'))) {
                $departureTime = $timetable->getNextDepartureTime($currentDate);
                
                if ($departureTime) {
                    $schedule->push([
                        'date' => $currentDate->toDateString(),
                        'departure_time' => $departureTime,
                        'is_available' => $departureTime->isFuture(),
                    ]);
                }
            }
            
            $currentDate->addDay();
        }

        return $schedule;
    }
}
