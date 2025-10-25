<?php

namespace Database\Seeders;

use App\Models\Discount;
use App\Models\Route;
use App\Models\User;
use App\Enums\RouteStatusEnum;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user for created_by
        $adminUser = User::whereHas('roles', function ($query) {
            $query->where('name', 'Admin');
        })->first();

        if (!$adminUser) {
            $adminUser = User::first();
        }

        // Get some routes
        $routes = Route::take(5)->get();

        if ($routes->isEmpty()) {
            $this->command->warn('No routes found. Creating a sample route...');
            $sampleRoute = Route::create([
                'name' => 'Sample Route',
                'code' => 'SR001',
                'operator_id' => $adminUser->id,
                'direction' => 'forward',
                'base_currency' => 'PKR',
                'status' => RouteStatusEnum::ACTIVE->value,
            ]);
            $routes = collect([$sampleRoute]);
        }

        $discounts = [
            // Early Bird Discount
            [
                'title' => 'Early Bird Special',
                'route_id' => $routes->isNotEmpty() ? $routes->random()->id : null,
                'discount_type' => 'percentage',
                'value' => 15.00,
                'is_android' => true,
                'is_ios' => true,
                'is_web' => true,
                'is_counter' => false,
                'starts_at' => now()->subDays(5),
                'ends_at' => now()->addDays(30),
                'start_time' => '06:00',
                'end_time' => '10:00',
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],

            // Weekend Discount
            [
                'title' => 'Weekend Getaway',
                'route_id' => $routes->isNotEmpty() ? $routes->random()->id : null,
                'discount_type' => 'fixed',
                'value' => 100.00,
                'is_android' => true,
                'is_ios' => true,
                'is_web' => true,
                'is_counter' => true,
                'starts_at' => now()->subDays(2),
                'ends_at' => now()->addDays(60),
                'start_time' => null,
                'end_time' => null,
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],

            // Mobile App Exclusive
            [
                'title' => 'Mobile App Exclusive',
                'route_id' => $routes->isNotEmpty() ? $routes->random()->id : null,
                'discount_type' => 'percentage',
                'value' => 20.00,
                'is_android' => true,
                'is_ios' => true,
                'is_web' => false,
                'is_counter' => false,
                'starts_at' => now(),
                'ends_at' => now()->addDays(45),
                'start_time' => null,
                'end_time' => null,
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],

            // Counter Booking Discount
            [
                'title' => 'Counter Booking Bonus',
                'route_id' => $routes->isNotEmpty() ? $routes->random()->id : null,
                'discount_type' => 'fixed',
                'value' => 50.00,
                'is_android' => false,
                'is_ios' => false,
                'is_web' => false,
                'is_counter' => true,
                'starts_at' => now()->subDays(10),
                'ends_at' => now()->addDays(20),
                'start_time' => '09:00',
                'end_time' => '17:00',
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],

            // Student Discount
            [
                'title' => 'Student Special',
                'route_id' => $routes->isNotEmpty() ? $routes->random()->id : null,
                'discount_type' => 'percentage',
                'value' => 25.00,
                'is_android' => true,
                'is_ios' => true,
                'is_web' => true,
                'is_counter' => true,
                'starts_at' => now()->subDays(15),
                'ends_at' => now()->addDays(90),
                'start_time' => null,
                'end_time' => null,
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],

            // Expired Discount (for testing)
            [
                'title' => 'Summer Special (Expired)',
                'route_id' => $routes->isNotEmpty() ? $routes->random()->id : null,
                'discount_type' => 'fixed',
                'value' => 75.00,
                'is_android' => true,
                'is_ios' => true,
                'is_web' => true,
                'is_counter' => true,
                'starts_at' => now()->subDays(60),
                'ends_at' => now()->subDays(30),
                'start_time' => null,
                'end_time' => null,
                'is_active' => true, // Still active but expired
                'created_by' => $adminUser->id,
            ],

            // Inactive Discount
            [
                'title' => 'Coming Soon Discount',
                'route_id' => $routes->isNotEmpty() ? $routes->random()->id : null,
                'discount_type' => 'percentage',
                'value' => 30.00,
                'is_android' => true,
                'is_ios' => true,
                'is_web' => true,
                'is_counter' => true,
                'starts_at' => now()->addDays(30),
                'ends_at' => now()->addDays(60),
                'start_time' => null,
                'end_time' => null,
                'is_active' => false,
                'created_by' => $adminUser->id,
            ],
        ];

        foreach ($discounts as $discountData) {
            Discount::create($discountData);
        }

        $this->command->info('Discounts seeded successfully!');
    }
}