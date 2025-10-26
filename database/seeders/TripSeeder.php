<?php

namespace Database\Seeders;

use App\Models\Trip;
use Illuminate\Database\Seeder;

class TripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some trips with buses assigned
        Trip::factory()
            ->count(20)
            ->scheduled()
            ->create();

        // Create some trips without buses
        Trip::factory()
            ->count(10)
            ->withoutBus()
            ->create();

        // Create some ongoing trips
        Trip::factory()
            ->count(5)
            ->ongoing()
            ->create();

        // Create some completed trips
        Trip::factory()
            ->count(15)
            ->completed()
            ->create();
    }
}
