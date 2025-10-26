<?php

namespace Database\Factories;

use App\Enums\ExpenseTypeEnum;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $type = fake()->randomElement(ExpenseTypeEnum::cases());

        return [
            'trip_id' => Trip::factory(),
            'type' => $type,
            'amount' => fake()->randomFloat(2, 100, 10000),
            'currency' => 'PKR',
            'description' => fake()->sentence(),
            'incurred_by' => User::factory(),
            'incurred_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'receipt_number' => $type->requiresReceipt() ? fake()->bothify('RCP-########') : null,
            'metadata' => null,
        ];
    }

    /**
     * Indicate that the expense is for fuel.
     */
    public function fuel(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ExpenseTypeEnum::Fuel,
            'receipt_number' => fake()->bothify('RCP-########'),
        ]);
    }

    /**
     * Indicate that the expense is for toll.
     */
    public function toll(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ExpenseTypeEnum::Toll,
            'receipt_number' => fake()->bothify('RCP-########'),
        ]);
    }

    /**
     * Indicate that the expense is for driver pay.
     */
    public function driverPay(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ExpenseTypeEnum::DriverPay,
        ]);
    }
}
