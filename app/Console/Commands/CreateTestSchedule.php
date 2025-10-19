<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Route;
use App\Models\Schedule;
use App\Enums\FrequencyTypeEnum;

class CreateTestSchedule extends Command
{
    protected $signature = 'test:create-schedule';
    protected $description = 'Create a test route schedule';

    public function handle()
    {
        // Get the first route
        $route = Route::first();
        
        if (!$route) {
            $this->error('No routes found. Please create a route first.');
            return;
        }

        // Create a test schedule
        $schedule = Schedule::create([
            'route_id' => $route->id,
            'trip_code' => 'TEST001',
            'departure_time' => '08:00:00',
            'arrival_time' => '12:00:00',
            'frequency' => FrequencyTypeEnum::DAILY->value,
            'operating_days' => null,
            'is_active' => true,
        ]);

        $this->info("Created test schedule: {$schedule->trip_code} for route: {$route->name}");
        
        // Show count
        $count = Schedule::count();
        $this->info("Total schedules in database: {$count}");
    }
}