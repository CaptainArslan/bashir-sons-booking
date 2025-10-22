<?php

namespace Database\Factories;

use App\Models\Discount;
use App\Models\Route;
use App\Models\User;
use App\Enums\DiscountTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Discount>
 */
class DiscountFactory extends Factory
{
    protected $model = Discount::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $discountType = $this->faker->randomElement(['fixed', 'percentage']);
        $value = match ($discountType) {
            'fixed' => $this->faker->numberBetween(10, 500),
            'percentage' => $this->faker->numberBetween(5, 50),
        };

        return [
            'title' => $this->faker->words(3, true) . ' Discount',
            'route_id' => Route::factory(),
            'discount_type' => $discountType,
            'value' => $value,
            'is_android' => $this->faker->boolean(80),
            'is_ios' => $this->faker->boolean(80),
            'is_web' => $this->faker->boolean(90),
            'is_counter' => $this->faker->boolean(70),
            'starts_at' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'ends_at' => $this->faker->dateTimeBetween('+1 month', '+3 months'),
            'start_time' => $this->faker->optional(0.7)->time('H:i'),
            'end_time' => $this->faker->optional(0.7)->time('H:i'),
            'is_active' => $this->faker->boolean(85),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the discount is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'starts_at' => now()->subDays(7),
            'ends_at' => now()->addDays(30),
        ]);
    }

    /**
     * Indicate that the discount is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the discount is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'starts_at' => now()->subDays(30),
            'ends_at' => now()->subDays(1),
        ]);
    }

    /**
     * Indicate that the discount is for Android only.
     */
    public function androidOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_android' => true,
            'is_ios' => false,
            'is_web' => false,
            'is_counter' => false,
        ]);
    }

    /**
     * Indicate that the discount is for all platforms.
     */
    public function allPlatforms(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_android' => true,
            'is_ios' => true,
            'is_web' => true,
            'is_counter' => true,
        ]);
    }

    /**
     * Indicate that the discount is a flat amount discount.
     */
    public function flat(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_type' => 'fixed',
            'value' => $this->faker->numberBetween(50, 200),
        ]);
    }

    /**
     * Indicate that the discount is a percentage discount.
     */
    public function percent(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_type' => 'percentage',
            'value' => $this->faker->numberBetween(10, 30),
        ]);
    }

    /**
     * Indicate that the discount has time restrictions.
     */
    public function withTimeRestrictions(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => $this->faker->time('H:i', '12:00'),
            'end_time' => $this->faker->time('H:i', '18:00'),
        ]);
    }
}