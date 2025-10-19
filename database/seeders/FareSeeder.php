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
        $this->command->newLine();

        // Calculate total possible pairs
        $totalPairs = 0;
        $terminalPairs = [];
        
        foreach ($terminals as $fromTerminal) {
            foreach ($terminals as $toTerminal) {
                if ($fromTerminal->id === $toTerminal->id) {
                    continue; // Skip same terminal
                }

                $pairKey = min($fromTerminal->id, $toTerminal->id) . '_' . max($fromTerminal->id, $toTerminal->id);

                if (!in_array($pairKey, $terminalPairs)) {
                    $terminalPairs[] = $pairKey;
                    $totalPairs++;
                }
            }
        }

        $this->command->info("Total fare pairs to create: {$totalPairs}");
        $this->command->newLine();

        // Initialize progress bar
        $progressBar = $this->command->getOutput()->createProgressBar($totalPairs);
        $progressBar->setFormat('verbose');
        $progressBar->setMessage('Starting fare creation...', 'status');
        $progressBar->start();

        $faresCreated = 0;
        $faresSkipped = 0;
        $processedPairs = [];

        // Create fares between different terminals
        foreach ($terminals as $fromTerminal) {
            foreach ($terminals as $toTerminal) {
                if ($fromTerminal->id === $toTerminal->id) {
                    continue; // Skip same terminal
                }

                $pairKey = min($fromTerminal->id, $toTerminal->id) . '_' . max($fromTerminal->id, $toTerminal->id);

                if (in_array($pairKey, $processedPairs)) {
                    continue; // Skip duplicate pairs
                }

                $processedPairs[] = $pairKey;

                // Update progress bar status
                $progressBar->setMessage(
                    "Creating fare: {$fromTerminal->city->name} → {$toTerminal->city->name}", 
                    'status'
                );

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

                try {
                    $fare = Fare::firstOrCreate(
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

                    if ($fare->wasRecentlyCreated) {
                        $faresCreated++;
                    } else {
                        $faresSkipped++;
                    }

                } catch (\Exception $e) {
                    $this->command->error("Error creating fare for {$fromTerminal->city->name} → {$toTerminal->city->name}: " . $e->getMessage());
                }

                // Advance progress bar
                $progressBar->advance();
            }
        }

        // Complete progress bar
        $progressBar->setMessage('Fare creation completed!', 'status');
        $progressBar->finish();
        $this->command->newLine(2);

        // Display summary
        $this->command->info("✅ Fare Seeding Summary:");
        $this->command->table(
            ['Metric', 'Count'],
            [
                ['Total Pairs Processed', $totalPairs],
                ['New Fares Created', $faresCreated],
                ['Existing Fares Skipped', $faresSkipped],
                ['Total Active Terminals', $terminals->count()],
            ]
        );

        if ($faresCreated > 0) {
            $this->command->info("🎉 Successfully created {$faresCreated} new fares!");
        } else {
            $this->command->warn("⚠️  No new fares were created. All fare pairs already exist.");
        }

        $this->command->newLine();
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
