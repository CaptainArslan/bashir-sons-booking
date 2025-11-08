<?php

namespace Database\Seeders;

use App\Models\Route;
use App\Models\Timetable;
use App\Models\TimetableStop;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TimetableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get active routes
        $routes = Route::with(['routeStops.terminal'])->where('status', 'active')->get();

        if ($routes->isEmpty()) {
            $this->command->warn('No active routes found. Please seed routes first.');

            return;
        }

        $totalTimetables = 0;
        $totalStops = 0;

        foreach ($routes as $route) {
            $routeStops = $route->routeStops()->orderBy('sequence')->get();

            if ($routeStops->isEmpty()) {
                $this->command->warn("Route '{$route->name}' has no stops. Skipping...");

                continue;
            }

            $stopCount = $routeStops->count();
            $totalStops += $stopCount;

            // Create 4-5 timetables for each route
            $timetableCount = rand(4, 5);
            $this->command->info("Creating {$timetableCount} timetables for route: {$route->name} ({$stopCount} stops)");

            // Define start times throughout the day (distributed evenly)
            $startTimes = $this->generateStartTimes($timetableCount);

            for ($i = 0; $i < $timetableCount; $i++) {
                $startTime = Carbon::parse($startTimes[$i]);

                $timetable = Timetable::create([
                    'route_id' => $route->id,
                    'name' => $route->name.' - Trip '.($i + 1),
                    'start_departure_time' => $startTime->format('H:i:s'),
                    'is_active' => true,
                ]);

                $currentTime = $startTime->copy();
                $lastArrivalTime = null;

                foreach ($routeStops as $index => $routeStop) {
                    $isFirstStop = $index === 0;
                    $isLastStop = $index === ($routeStops->count() - 1);

                    $arrivalTime = null;
                    $departureTime = null;

                    if ($isFirstStop) {
                        // First stop - arrival time (bus arrives at terminal) and departure time
                        // Arrival is typically 5-10 minutes before departure for first stop
                        $arrivalTimeMinutes = rand(5, 10);
                        $arrivalTime = $currentTime->copy()->subMinutes($arrivalTimeMinutes)->format('H:i:s');
                        $departureTime = $currentTime->format('H:i:s');
                    } else {
                        // Calculate travel time based on route length
                        // Longer routes have longer travel times between stops
                        $travelMinutes = $this->calculateTravelTime($stopCount, $index);

                        // Add travel time from previous stop
                        $currentTime->addMinutes($travelMinutes);
                        $arrivalTime = $currentTime->format('H:i:s');
                        $lastArrivalTime = $arrivalTime;

                        // Add stop time (waiting/loading passengers) - except for last stop
                        if (! $isLastStop) {
                            $stopMinutes = $this->calculateStopTime($index, $stopCount);
                            $currentTime->addMinutes($stopMinutes);
                            $departureTime = $currentTime->format('H:i:s');
                        }
                    }

                    TimetableStop::create([
                        'timetable_id' => $timetable->id,
                        'terminal_id' => $routeStop->terminal_id,
                        'sequence' => $routeStop->sequence,
                        'arrival_time' => $arrivalTime,
                        'departure_time' => $departureTime,
                        'is_active' => true,
                    ]);
                }

                // Update timetable end arrival time (last stop)
                if ($lastArrivalTime) {
                    $timetable->update([
                        'end_arrival_time' => $lastArrivalTime,
                    ]);
                }

                $totalTimetables++;
            }
        }

        $this->command->newLine();
        $this->command->info('✅ Timetable Seeding Summary:');
        $this->command->table(
            ['Metric', 'Count'],
            [
                ['Total Routes Processed', $routes->count()],
                ['Total Timetables Created', $totalTimetables],
                ['Total Stops Processed', $totalStops],
            ]
        );
        $this->command->info('🎉 Timetables seeded successfully with proper time differences!');
        $this->command->newLine();
    }

    /**
     * Generate start times distributed throughout the day
     */
    private function generateStartTimes(int $count): array
    {
        $times = [];

        // Distribute times from 6:00 AM to 10:00 PM
        $startHour = 6;
        $endHour = 22;
        $totalHours = $endHour - $startHour;

        if ($count === 1) {
            $times[] = '09:00';
        } else {
            $interval = $totalHours / ($count + 1);

            for ($i = 1; $i <= $count; $i++) {
                $hour = $startHour + ($interval * $i);
                $hourInt = (int) $hour;
                $minutes = (int) (($hour - $hourInt) * 60);

                // Round to nearest 15 minutes
                $minutes = round($minutes / 15) * 15;
                if ($minutes >= 60) {
                    $hourInt++;
                    $minutes = 0;
                }

                $times[] = sprintf('%02d:%02d', $hourInt, $minutes);
            }
        }

        return $times;
    }

    /**
     * Calculate travel time between stops based on route characteristics
     */
    private function calculateTravelTime(int $totalStops, int $currentIndex): int
    {
        // Base travel time: 20-30 minutes for longer routes, 15-25 for shorter
        if ($totalStops >= 5) {
            // Longer routes: 25-35 minutes between stops
            $baseTime = rand(25, 35);
        } elseif ($totalStops >= 3) {
            // Medium routes: 20-30 minutes between stops
            $baseTime = rand(20, 30);
        } else {
            // Short routes: 15-25 minutes between stops
            $baseTime = rand(15, 25);
        }

        // Add slight variation (±5 minutes) for realism
        $variation = rand(-5, 5);

        return max(10, $baseTime + $variation); // Minimum 10 minutes
    }

    /**
     * Calculate stop time (waiting/loading) at each terminal
     */
    private function calculateStopTime(int $currentIndex, int $totalStops): int
    {
        // First few stops: longer wait (more passengers boarding)
        if ($currentIndex <= 2) {
            return rand(8, 12); // 8-12 minutes
        }

        // Middle stops: moderate wait
        if ($currentIndex < $totalStops - 2) {
            return rand(5, 8); // 5-8 minutes
        }

        // Last few stops: shorter wait (mostly drop-offs)
        return rand(3, 6); // 3-6 minutes
    }
}
