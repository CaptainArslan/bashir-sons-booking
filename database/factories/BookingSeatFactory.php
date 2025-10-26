<?php

namespace Database\Factories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BookingSeat>
 */
class BookingSeatFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $row = fake()->numberBetween(1, 12);
        $column = fake()->randomElement(['A', 'B', 'C', 'D']);
        $seatNumber = $row.$column;

        return [
            'booking_id' => Booking::factory(),
            'seat_number' => $seatNumber,
            'seat_row' => (string) $row,
            'seat_column' => $column,
            'passenger_name' => fake()->name(),
            'passenger_age' => fake()->numberBetween(5, 80),
            'passenger_gender' => fake()->randomElement(['male', 'female', 'other']),
            'passenger_cnic' => fake()->optional()->numerify('#############'),
            'passenger_phone' => fake()->optional()->phoneNumber(),
            'fare' => fake()->randomFloat(2, 500, 3000),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
