<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\FacilitySeeder;
use Database\Seeders\BusLayoutSeeder;
use Database\Seeders\BusTypeSeeder;
use Database\Seeders\BusSeeder;
use Database\Seeders\CitySeeder;
use Database\Seeders\TerminalSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            CitySeeder::class,
            TerminalSeeder::class,
            BusLayoutSeeder::class,
            BusTypeSeeder::class,
            FacilitySeeder::class,
            BusSeeder::class,
            BannerSeeder::class,
        ]);
    }
}
