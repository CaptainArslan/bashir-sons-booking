<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Route;
use App\Models\RouteTimetable;
use App\Enums\FrequencyTypeEnum;

class CreateTestTimetable extends Command
{
    protected $signature = 'test:create-timetable';
    protected $description = 'Create a test route timetable';

    public function handle()
    {
        // Get the first route
        $route = Route::first();
        
        if (!$route) {
            $this->error('No routes found. Please create a route first.');
            return;
        }

        // Create a test timetable
        $timetable = RouteTimetable::create([
            'route_id' => $route->id,
            'trip_code' => 'TEST001',
            'departure_time' => '08:00:00',
            'arrival_time' => '12:00:00',
            'frequency' => FrequencyTypeEnum::DAILY->value,
            'operating_days' => null,
            'is_active' => true,
        ]);

        $this->info("Created test timetable: {$timetable->trip_code} for route: {$route->name}");
        
        // Show count
        $count = RouteTimetable::count();
        $this->info("Total timetables in database: {$count}");
    }
}