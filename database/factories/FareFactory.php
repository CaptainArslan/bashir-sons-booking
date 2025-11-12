<?php

namespace Database\Factories;

use App\Enums\DiscountTypeEnum;
use App\Enums\FareStatusEnum;
use App\Models\Fare;
use App\Models\Terminal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Fare>
 */
class FareFactory extends Factory
{
    protected $model = Fare::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $baseFare = $this->faker->numberBetween(100, 5000);
        $discountType = $this->faker->randomElement(['flat', 'percent']);
        $discountValue = $discountType === 'percent'
            ? $this->faker->numberBetween(5, 25)
            : $this->faker->numberBetween(50, 500);

        $finalFare = $this->calculateFinalFare($baseFare, $discountType, $discountValue);

        return [
            'from_terminal_id' => Terminal::factory(),
            'to_terminal_id' => Terminal::factory(),
            'base_fare' => $baseFare,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'final_fare' => $finalFare,
            'currency' => $this->faker->randomElement(['PKR', 'USD', 'EUR']),
            'status' => $this->faker->randomElement(FareStatusEnum::getStatuses()),
        ];
    }

    /**
     * Create fare for specific terminals
     */
    public function forTerminals(Terminal $fromTerminal, Terminal $toTerminal): static
    {
        return $this->state(fn (array $attributes) => [
            'from_terminal_id' => $fromTerminal->id,
            'to_terminal_id' => $toTerminal->id,
        ]);
    }

    /**
     * Create active fare
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => FareStatusEnum::ACTIVE->value,
        ]);
    }

    /**
     * Create inactive fare
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => FareStatusEnum::INACTIVE->value,
        ]);
    }

    /**
     * Create fare with flat discount
     */
    public function withFlatDiscount(): static
    {
        $baseFare = $this->faker->numberBetween(100, 5000);
        $discountValue = $this->faker->numberBetween(50, min(500, (int) ($baseFare * 0.3)));
        $finalFare = max(0, $baseFare - $discountValue);

        return $this->state(fn (array $attributes) => [
            'base_fare' => $baseFare,
            'discount_type' => DiscountTypeEnum::FLAT->value,
            'discount_value' => $discountValue,
            'final_fare' => $finalFare,
        ]);
    }

    /**
     * Create fare with percentage discount
     */
    public function withPercentDiscount(): static
    {
        $baseFare = $this->faker->numberBetween(100, 5000);
        $discountValue = $this->faker->numberBetween(5, 25);
        $finalFare = max(0, (int) round($baseFare - ($baseFare * $discountValue / 100)));

        return $this->state(fn (array $attributes) => [
            'base_fare' => $baseFare,
            'discount_type' => DiscountTypeEnum::PERCENT->value,
            'discount_value' => $discountValue,
            'final_fare' => $finalFare,
        ]);
    }

    /**
     * Create fare without discount
     */
    public function withoutDiscount(): static
    {
        $baseFare = $this->faker->numberBetween(100, 5000);

        return $this->state(fn (array $attributes) => [
            'base_fare' => $baseFare,
            'discount_type' => null,
            'discount_value' => null,
            'final_fare' => $baseFare,
        ]);
    }

    /**
     * Calculate final fare based on discount
     */
    private function calculateFinalFare(int $baseFare, string $discountType, int $discountValue): int
    {
        if (! $discountType || ! $discountValue) {
            return $baseFare;
        }

        return match ($discountType) {
            'flat' => max(0, $baseFare - $discountValue),
            'percent' => max(0, (int) round($baseFare - ($baseFare * $discountValue / 100))),
            default => $baseFare,
        };
    }
}
