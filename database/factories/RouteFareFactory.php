<?php

namespace Database\Factories;

use App\Enums\RouteFareStatusEnum;
use App\Models\Route;
use App\Models\RouteStop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RouteFare>
 */
class RouteFareFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $baseFare = $this->faker->randomFloat(2, 100, 5000);
        $discountType = $this->faker->randomElement(['flat', 'percent', null]);
        $discountValue = $discountType ? 
            ($discountType === 'percent' ? $this->faker->randomFloat(2, 5, 25) : $this->faker->randomFloat(2, 50, 500)) : 
            null;
        
        $finalFare = $this->calculateFinalFare($baseFare, $discountType, $discountValue);

        return [
            'route_id' => Route::factory(),
            'from_stop_id' => RouteStop::factory(),
            'to_stop_id' => RouteStop::factory(),
            'base_fare' => $baseFare,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'final_fare' => $finalFare,
            'status' => $this->faker->randomElement(RouteFareStatusEnum::getStatuses()),
        ];
    }

    /**
     * Create fare for specific route and stops
     */
    public function forRouteAndStops(Route $route, RouteStop $fromStop, RouteStop $toStop): static
    {
        return $this->state(fn (array $attributes) => [
            'route_id' => $route->id,
            'from_stop_id' => $fromStop->id,
            'to_stop_id' => $toStop->id,
        ]);
    }

    /**
     * Create active fare
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RouteFareStatusEnum::ACTIVE->value,
        ]);
    }

    /**
     * Create inactive fare
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RouteFareStatusEnum::INACTIVE->value,
        ]);
    }

    /**
     * Create fare with flat discount
     */
    public function withFlatDiscount(float $discountAmount): static
    {
        return $this->state(function (array $attributes) use ($discountAmount) {
            $baseFare = $attributes['base_fare'] ?? $this->faker->randomFloat(2, 100, 5000);
            $finalFare = max(0, $baseFare - $discountAmount);
            
            return [
                'discount_type' => 'flat',
                'discount_value' => $discountAmount,
                'final_fare' => $finalFare,
            ];
        });
    }

    /**
     * Create fare with percentage discount
     */
    public function withPercentDiscount(float $discountPercent): static
    {
        return $this->state(function (array $attributes) use ($discountPercent) {
            $baseFare = $attributes['base_fare'] ?? $this->faker->randomFloat(2, 100, 5000);
            $finalFare = max(0, $baseFare - ($baseFare * $discountPercent / 100));
            
            return [
                'discount_type' => 'percent',
                'discount_value' => $discountPercent,
                'final_fare' => $finalFare,
            ];
        });
    }

    /**
     * Calculate final fare based on discount
     */
    private function calculateFinalFare(float $baseFare, ?string $discountType, ?float $discountValue): float
    {
        if (!$discountType || !$discountValue) {
            return $baseFare;
        }

        return match ($discountType) {
            'flat' => max(0, $baseFare - $discountValue),
            'percent' => max(0, $baseFare - ($baseFare * $discountValue / 100)),
            default => $baseFare,
        };
    }
}
