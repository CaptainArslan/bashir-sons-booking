<?php

namespace Database\Factories;

use App\Enums\SeatLockTypeEnum;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SeatLock>
 */
class SeatLockFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $row = fake()->numberBetween(1, 12);
        $column = fake()->randomElement(['A', 'B', 'C', 'D']);
        $seatId = $row.$column;
        $lockedAt = now();

        return [
            'trip_id' => Trip::factory(),
            'seat_id' => $seatId,
            'seat_number' => $seatId,
            'seat_row' => (string) $row,
            'seat_column' => $column,
            'lock_type' => fake()->randomElement(SeatLockTypeEnum::cases()),
            'locked_at' => $lockedAt,
            'expires_at' => $lockedAt->addMinutes(5),
            'released_at' => null,
            'metadata' => json_encode(['session_id' => fake()->uuid()]),
        ];
    }

    /**
     * Indicate that the lock is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->addMinutes(5),
            'released_at' => null,
        ]);
    }

    /**
     * Indicate that the lock is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subMinutes(5),
            'released_at' => null,
        ]);
    }

    /**
     * Indicate that the lock is released.
     */
    public function released(): static
    {
        return $this->state(fn (array $attributes) => [
            'released_at' => now(),
        ]);
    }
}
