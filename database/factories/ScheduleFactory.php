<?php

namespace Database\Factories;

use App\Enums\FrequencyTypeEnum;
use App\Models\Route;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $departureTime = $this->faker->time('H:i');
        $arrivalTime = $this->faker->optional(0.8)->time('H:i');
        
        // Ensure arrival time is after departure time
        if ($arrivalTime && $arrivalTime <= $departureTime) {
            $arrivalTime = $this->faker->time('H:i', '+2 hours');
        }

        $frequency = $this->faker->randomElement(FrequencyTypeEnum::cases());
        $operatingDays = null;

        if ($frequency === FrequencyTypeEnum::CUSTOM) {
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            $operatingDays = $this->faker->randomElements($days, $this->faker->numberBetween(2, 5));
        }

        return [
            'route_id' => Route::factory(),
            'trip_code' => $this->faker->unique()->regexify('[A-Z]{2}[0-9]{3}'),
            'departure_time' => $departureTime,
            'arrival_time' => $arrivalTime,
            'frequency' => $frequency,
            'operating_days' => $operatingDays,
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
        ];
    }

    /**
     * Indicate that the schedule is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the schedule is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the schedule operates daily.
     */
    public function daily(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => FrequencyTypeEnum::DAILY,
            'operating_days' => null,
        ]);
    }

    /**
     * Indicate that the schedule operates on weekdays only.
     */
    public function weekdays(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => FrequencyTypeEnum::WEEKDAYS,
            'operating_days' => null,
        ]);
    }

    /**
     * Indicate that the schedule operates on weekends only.
     */
    public function weekends(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => FrequencyTypeEnum::WEEKENDS,
            'operating_days' => null,
        ]);
    }

    /**
     * Indicate that the schedule operates on custom days.
     */
    public function custom(array $days = null): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => FrequencyTypeEnum::CUSTOM,
            'operating_days' => $days ?? $this->faker->randomElements(
                ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
                $this->faker->numberBetween(2, 5)
            ),
        ]);
    }
}
