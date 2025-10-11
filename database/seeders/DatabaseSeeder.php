<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\FacilitySeeder;
use Database\Seeders\BusLayoutSeeder;
use Database\Seeders\BusTypeSeeder;
use Database\Seeders\BusSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            UserSeeder::class,
            FacilitySeeder::class,
            BusLayoutSeeder::class,
            BusTypeSeeder::class,
            BusSeeder::class,
        ]);
    }
}
