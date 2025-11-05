<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Timetable;
use App\Models\TimetableStop;
use App\Models\Route;
use Carbon\Carbon;

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

        foreach ($routes as $route) {
            $routeStops = $route->routeStops()->orderBy('sequence')->get();

            if ($routeStops->isEmpty()) {
                continue;
            }

            // Create 3 timetables for each route
            for ($i = 1; $i <= 3; $i++) {
                $startTime = Carbon::parse('06:00')->addHours($i * 2); // e.g. 08:00, 10:00, 12:00

                $timetable = Timetable::create([
                    'route_id' => $route->id,
                    'name' => $route->name . ' - Trip ' . $i,
                    'start_departure_time' => $startTime->format('H:i:s'),
                    'is_active' => true,
                ]);

                $currentTime = $startTime->copy();

                foreach ($routeStops as $index => $routeStop) {
                    $isFirstStop = $index === 0;
                    $isLastStop = $index === ($routeStops->count() - 1);

                    $arrivalTime = null;
                    $departureTime = null;

                    if ($isFirstStop) {
                        // First stop - only departure
                        $departureTime = $currentTime->format('H:i:s');
                    } else {
                        // Add 15 mins travel time between each stop
                        $currentTime->addMinutes(15);
                        $arrivalTime = $currentTime->format('H:i:s');

                        // Add 5 mins stop time (waiting/loading)
                        if (!$isLastStop) {
                            $currentTime->addMinutes(5);
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
                $lastStop = $timetable->timetableStops()->orderByDesc('sequence')->first();
                if ($lastStop && $lastStop->arrival_time) {
                    $timetable->update([
                        'end_arrival_time' => $lastStop->arrival_time
                    ]);
                }
            }
        }

        $this->command->info('Timetables seeded successfully with realistic time gaps!');
    }
}
