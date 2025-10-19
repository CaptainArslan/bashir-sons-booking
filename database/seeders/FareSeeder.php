<?php

namespace Database\Seeders;

use App\Models\Fare;
use App\Models\Terminal;
use App\Enums\FareStatusEnum;
use App\Enums\DiscountTypeEnum;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class FareSeeder extends Seeder
{
    protected $faker;

    public function __construct()
    {
        $this->faker = Faker::create();
    }
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $terminals = Terminal::where('status', 'active')->get();

        if ($terminals->count() < 2) {
            $this->command->warn('Not enough terminals to create fares. Please seed terminals first.');
            return;
        }

        $this->command->info('Creating fares between terminals...');

        $faresCreated = 0;
        $terminalPairs = [];

        // Create fares between different terminals
        foreach ($terminals as $fromTerminal) {
            foreach ($terminals as $toTerminal) {
                if ($fromTerminal->id === $toTerminal->id) {
                    continue; // Skip same terminal
                }

                $pairKey = min($fromTerminal->id, $toTerminal->id) . '_' . max($fromTerminal->id, $toTerminal->id);

                if (in_array($pairKey, $terminalPairs)) {
                    continue; // Skip duplicate pairs
                }

                $terminalPairs[] = $pairKey;

                $baseFare = $this->calculateBaseFare($fromTerminal, $toTerminal);
                $discountType = $this->faker->randomElement(['flat', 'percent', null]);
                $discountValue = null;
                $finalFare = $baseFare;

                if ($discountType) {
                    $discountValue = $discountType === 'percent'
                        ? $this->faker->randomFloat(2, 5, 20)
                        : $this->faker->randomFloat(2, 50, min(200, $baseFare * 0.2));

                    $finalFare = $this->calculateFinalFare($baseFare, $discountType, $discountValue);
                }

                Fare::firstOrCreate(
                    [
                        'from_terminal_id' => $fromTerminal->id,
                        'to_terminal_id' => $toTerminal->id,
                    ],
                    [
                        'base_fare' => $baseFare,
                        'discount_type' => $discountType ?? 'flat',
                        'discount_value' => $discountValue ?? 0,
                        'final_fare' => $finalFare,
                        'currency' => 'PKR',
                        'status' => $this->faker->randomElement(FareStatusEnum::getStatuses()),
                    ]
                );

                $faresCreated++;
            }
        }

        $this->command->info("Created {$faresCreated} fares successfully!");
    }

    /**
     * Calculate base fare based on terminal distance/city
     */
    private function calculateBaseFare(Terminal $fromTerminal, Terminal $toTerminal): float
    {
        // Simple calculation based on city names (you can enhance this with actual distance)
        $fromCity = $fromTerminal->city->name;
        $toCity = $toTerminal->city->name;

        // Base fare ranges
        $baseFares = [
            'Karachi' => ['Lahore' => 2500, 'Islamabad' => 2000, 'Peshawar' => 1800],
            'Lahore' => ['Karachi' => 2500, 'Islamabad' => 800, 'Peshawar' => 1200],
            'Islamabad' => ['Karachi' => 2000, 'Lahore' => 800, 'Peshawar' => 400],
            'Peshawar' => ['Karachi' => 1800, 'Lahore' => 1200, 'Islamabad' => 400],
        ];

        // Check if we have predefined fares
        if (isset($baseFares[$fromCity][$toCity])) {
            return $baseFares[$fromCity][$toCity];
        }

        // Default fare calculation
        return $this->faker->randomFloat(2, 500, 3000);
    }

    /**
     * Calculate final fare based on discount
     */
    private function calculateFinalFare(float $baseFare, string $discountType, float $discountValue): float
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
