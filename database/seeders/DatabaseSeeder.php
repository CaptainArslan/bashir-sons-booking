<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\FacilitySeeder;
use Database\Seeders\BusLayoutSeeder;
use Database\Seeders\BusTypeSeeder;
use Database\Seeders\CitySeeder;
use Database\Seeders\FareSeeder;

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
            DefaultTerminalSeeder::class,
            DefaultRouteSeeder::class,
            FareSeeder::class,
            // AnnouncementSeeder::class,
            // DiscountSeeder::class,
            // TerminalSeeder::class,
            // RouteSeeder::class,
            // TimetableSeeder::class,
            // TimetableStopSeeder::class,
            // FareSeeder::class,
            // BusSeeder::class,
            // BannerSeeder::class,
            // GeneralSettingSeeder::class,
        ]);
    }
}
