<?php

namespace App\Services;

use App\Models\Route;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ScheduleService
{
    /**
     * Get available schedules for booking on a specific date.
     */
    public function getAvailableSchedulesForDate(Route $route, Carbon $date): Collection
    {
        return $route->schedules()
            ->where('is_active', true)
            ->get()
            ->filter(function (Schedule $schedule) use ($date) {
                return $schedule->operatesOn($date->format('l')) && 
                       $schedule->getNextDepartureTime($date) !== null;
            });
    }

    /**
     * Get schedule for a specific date range.
     */
    public function getScheduleForDateRange(Schedule $schedule, Carbon $startDate, Carbon $endDate): Collection
    {
        $scheduleData = collect();
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            if ($schedule->operatesOn($currentDate->format('l'))) {
                $departureTime = $schedule->getNextDepartureTime($currentDate);
                
                if ($departureTime) {
                    $scheduleData->push([
                        'date' => $currentDate->toDateString(),
                        'departure_time' => $departureTime,
                        'is_available' => $departureTime->isFuture(),
                    ]);
                }
            }
            
            $currentDate->addDay();
        }

        return $scheduleData;
    }
}
