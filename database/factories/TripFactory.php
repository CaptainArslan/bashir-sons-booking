<?php

namespace Database\Factories;

use App\Enums\TripStatusEnum;
use App\Models\Bus;
use App\Models\Route;
use App\Models\Timetable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trip>
 */
class TripFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $departureDate = fake()->dateTimeBetween('now', '+30 days');
        $departureTime = fake()->time('H:i:s');
        $departureDatetime = date('Y-m-d', $departureDate->getTimestamp()).' '.$departureTime;

        return [
            'timetable_id' => Timetable::factory(),
            'route_id' => Route::factory(),
            'bus_id' => Bus::factory(),
            'departure_date' => $departureDate,
            'departure_datetime' => $departureDatetime,
            'estimated_arrival_datetime' => fake()->dateTimeBetween($departureDatetime, '+1 day'),
            'status' => fake()->randomElement(TripStatusEnum::cases()),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the trip has no bus assigned.
     */
    public function withoutBus(): static
    {
        return $this->state(fn (array $attributes) => [
            'bus_id' => null,
            'status' => TripStatusEnum::Pending,
        ]);
    }

    /**
     * Indicate that the trip is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TripStatusEnum::Pending,
        ]);
    }

    /**
     * Indicate that the trip is scheduled.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TripStatusEnum::Scheduled,
        ]);
    }

    /**
     * Indicate that the trip is ongoing.
     */
    public function ongoing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TripStatusEnum::Ongoing,
        ]);
    }

    /**
     * Indicate that the trip is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TripStatusEnum::Completed,
        ]);
    }
}
