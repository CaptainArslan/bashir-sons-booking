<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            CitySeeder::class,
            BusLayoutSeeder::class,
            BusTypeSeeder::class,
            FacilitySeeder::class,
            AnnouncementSeeder::class,
            DiscountSeeder::class,
            TerminalSeeder::class,
            RouteSeeder::class,
            // TimetableSeeder::class,
            // TimetableStopSeeder::class,
            // FareSeeder::class,
            // BusSeeder::class,
            // BannerSeeder::class,
            // GeneralSettingSeeder::class,
        ]);
    }
}
