<?php

namespace Database\Factories;

use App\Enums\BookingStatusEnum;
use App\Enums\BookingTypeEnum;
use App\Models\RouteStop;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $totalFare = fake()->randomFloat(2, 500, 5000);
        $discountAmount = fake()->randomFloat(2, 0, $totalFare * 0.2);

        return [
            'booking_number' => 'BKG-'.strtoupper(fake()->bothify('????????')),
            'trip_id' => Trip::factory(),
            'user_id' => User::factory(),
            'booked_by_user_id' => User::factory(),
            'from_stop_id' => RouteStop::factory(),
            'to_stop_id' => RouteStop::factory(),
            'type' => fake()->randomElement(BookingTypeEnum::cases()),
            'status' => fake()->randomElement(BookingStatusEnum::cases()),
            'total_fare' => $totalFare,
            'discount_amount' => $discountAmount,
            'final_amount' => $totalFare - $discountAmount,
            'currency' => 'PKR',
            'total_passengers' => fake()->numberBetween(1, 4),
            'passenger_contact_phone' => fake()->phoneNumber(),
            'passenger_contact_email' => fake()->email(),
            'passenger_contact_name' => fake()->name(),
            'notes' => fake()->optional()->sentence(),
            'metadata' => null,
            'confirmed_at' => fake()->optional()->dateTimeBetween('-30 days', 'now'),
            'cancelled_at' => null,
        ];
    }

    /**
     * Indicate that the booking is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatusEnum::Confirmed,
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Indicate that the booking is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatusEnum::Pending,
            'confirmed_at' => null,
        ]);
    }

    /**
     * Indicate that the booking is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatusEnum::Cancelled,
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Indicate that the booking is online.
     */
    public function online(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => BookingTypeEnum::Online,
        ]);
    }

    /**
     * Indicate that the booking is counter.
     */
    public function counter(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => BookingTypeEnum::Counter,
        ]);
    }

    /**
     * Indicate that the booking is phone.
     */
    public function phone(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => BookingTypeEnum::Phone,
        ]);
    }
}
