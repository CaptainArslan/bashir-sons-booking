<?php

namespace Database\Seeders;

use App\Enums\FrequencyTypeEnum;
use App\Models\Route;
use App\Models\Schedule;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all active routes
        $routes = Route::where('status', 'active')->get();

        if ($routes->isEmpty()) {
            $this->command->warn('No active routes found. Please run RouteSeeder first.');
            return;
        }

        $this->command->info('Creating schedules for ' . $routes->count() . ' routes...');

        foreach ($routes as $route) {
            // Create 2-4 schedules per route
            $scheduleCount = fake()->numberBetween(2, 4);

            for ($i = 0; $i < $scheduleCount; $i++) {
                $this->createScheduleForRoute($route, $i + 1);
            }
        }

        $this->command->info('Schedules created successfully!');
    }

    /**
     * Create a schedule for a specific route.
     */
    private function createScheduleForRoute(Route $route, int $sequence): void
    {
        // $departureTime = fake()->time('H:i');
        // $arrivalTime = fake()->optional(0.8)->time('H:i');

        // // Ensure arrival time is after departure time
        // if ($arrivalTime && $arrivalTime <= $departureTime) {
        //     $arrivalTime = fake()->time('H:i', '+2 hours');
        // }

        $frequency = fake()->randomElement(FrequencyTypeEnum::cases());
        $operatingDays = null;

        if ($frequency === FrequencyTypeEnum::CUSTOM) {
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            $operatingDays = fake()->randomElements($days, fake()->numberBetween(2, 5));
        }

        Schedule::create([
            'route_id' => $route->id,
            'name' => $route->name . ' - ' . $sequence,
            'code' => $route->code . '-' . str_pad($sequence, 2, '0', STR_PAD_LEFT),
            // 'departure_time' => $departureTime,
            // 'arrival_time' => $arrivalTime,
            'frequency' => $frequency,
            'operating_days' => $operatingDays,
            'is_active' => fake()->boolean(85), // 85% chance of being active
        ]);
    }
}
