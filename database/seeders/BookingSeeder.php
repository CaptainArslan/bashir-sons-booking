<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingSeat;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create confirmed bookings with seats
        Booking::factory()
            ->count(30)
            ->confirmed()
            ->create()
            ->each(function ($booking) {
                BookingSeat::factory()
                    ->count($booking->total_passengers)
                    ->create([
                        'booking_id' => $booking->id,
                    ]);
            });

        // Create pending bookings with seats
        Booking::factory()
            ->count(10)
            ->pending()
            ->create()
            ->each(function ($booking) {
                BookingSeat::factory()
                    ->count($booking->total_passengers)
                    ->create([
                        'booking_id' => $booking->id,
                    ]);
            });

        // Create some phone bookings
        Booking::factory()
            ->count(5)
            ->phone()
            ->pending()
            ->create()
            ->each(function ($booking) {
                BookingSeat::factory()
                    ->count($booking->total_passengers)
                    ->create([
                        'booking_id' => $booking->id,
                    ]);
            });
    }
}
