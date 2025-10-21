<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TimetableStop;

class TimetableStopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // This seeder is handled by TimetableSeeder
        // TimetableStopSeeder is kept for future use if needed
        $this->command->info('TimetableStopSeeder: Use TimetableSeeder to create timetable stops.');
    }
}
