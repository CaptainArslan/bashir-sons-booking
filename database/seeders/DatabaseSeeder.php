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
            BusTypeSeeder::class,
            BusLayoutSeeder::class,
            FacilitySeeder::class,
            BusSeeder::class,
            DefaultTerminalSeeder::class,
            DefaultRouteSeeder::class,
            // AnnouncementSeeder::class,
            // TerminalSeeder::class,
            // RouteSeeder::class,
            // DiscountSeeder::class,
            // TimetableSeeder::class,
            // TimetableStopSeeder::class,
            // FareSeeder::class,
            // BusSeeder::class,
            // BannerSeeder::class,
            // GeneralSettingSeeder::class,
        ]);
    }
}
