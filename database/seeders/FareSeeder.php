<?php

namespace Database\Seeders;

use App\Enums\FareStatusEnum;
use App\Models\Fare;
use App\Models\Route;
use App\Models\RouteStop;
use Illuminate\Database\Seeder;

class FareSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $routes = Route::where('status', 'active')->with('routeStops.terminal.city')->get();

        if ($routes->isEmpty()) {
            $this->command->warn('No active routes found. Please seed routes first.');

            return;
        }

        $this->command->info('Creating fares for all route stop combinations...');
        $this->command->newLine();

        // Calculate total possible fare pairs
        $totalPairs = 0;
        foreach ($routes as $route) {
            $stopCount = $route->routeStops->count();
            // For each route, calculate combinations: from any stop to any later stop
            $totalPairs += ($stopCount * ($stopCount - 1)) / 2;
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

        // Create fares for each route
        foreach ($routes as $route) {
            $routeStops = $route->routeStops->sortBy('sequence')->values();

            if ($routeStops->count() < 2) {
                $this->command->warn("Route '{$route->name}' has less than 2 stops. Skipping...");

                continue;
            }

            // Create fares between all stop combinations in sequence order
            for ($i = 0; $i < $routeStops->count(); $i++) {
                $fromStop = $routeStops[$i];

                // Only create fares to stops that come after this stop in the sequence
                for ($j = $i + 1; $j < $routeStops->count(); $j++) {
                    $toStop = $routeStops[$j];

                    $pairKey = $fromStop->terminal_id.'_'.$toStop->terminal_id;

                    // Skip if we've already processed this terminal pair
                    if (in_array($pairKey, $processedPairs)) {
                        $progressBar->advance();

                        continue;
                    }

                    $processedPairs[] = $pairKey;

                    // Update progress bar status
                    $fromTerminalName = $fromStop->terminal->name ?? 'Unknown';
                    $toTerminalName = $toStop->terminal->name ?? 'Unknown';
                    $fromCityName = $fromStop->terminal->city->name ?? 'Unknown';
                    $toCityName = $toStop->terminal->city->name ?? 'Unknown';

                    $progressBar->setMessage(
                        "Route: {$route->name} | {$fromCityName} → {$toCityName}",
                        'status'
                    );

                    // Calculate base fare based on sequence difference (more stops = higher fare)
                    $sequenceDiff = $toStop->sequence - $fromStop->sequence;
                    $baseFare = $this->calculateBaseFare($fromStop, $toStop, $sequenceDiff);

                    // Randomly decide if this fare has a discount
                    $discountType = \fake()->randomElement(['flat', 'percent', null]);
                    $discountValue = null;
                    $finalFare = $baseFare;

                    if ($discountType) {
                        $discountValue = $discountType === 'percent'
                            ? \fake()->randomFloat(2, 5, 20)
                            : \fake()->randomFloat(2, 50, min(200, $baseFare * 0.2));

                        $finalFare = $this->calculateFinalFare($baseFare, $discountType, $discountValue);
                    }

                    try {
                        $fare = Fare::firstOrCreate(
                            [
                                'from_terminal_id' => $fromStop->terminal_id,
                                'to_terminal_id' => $toStop->terminal_id,
                            ],
                            [
                                'base_fare' => $baseFare,
                                'discount_type' => $discountType ?? 'flat',
                                'discount_value' => $discountValue ?? 0,
                                'final_fare' => $finalFare,
                                'currency' => 'PKR',
                                'status' => FareStatusEnum::ACTIVE->value,
                            ]
                        );

                        if ($fare->wasRecentlyCreated) {
                            $faresCreated++;
                        } else {
                            $faresSkipped++;
                        }
                    } catch (\Exception $e) {
                        $this->command->error("Error creating fare for {$fromCityName} → {$toCityName}: ".$e->getMessage());
                    }

                    // Advance progress bar
                    $progressBar->advance();
                }
            }
        }

        // Complete progress bar
        $progressBar->setMessage('Fare creation completed!', 'status');
        $progressBar->finish();
        $this->command->newLine(2);

        // Display summary
        $this->command->info('✅ Fare Seeding Summary:');
        $this->command->table(
            ['Metric', 'Count'],
            [
                ['Total Routes Processed', $routes->count()],
                ['Total Pairs Processed', $totalPairs],
                ['New Fares Created', $faresCreated],
                ['Existing Fares Skipped', $faresSkipped],
            ]
        );

        if ($faresCreated > 0) {
            $this->command->info("🎉 Successfully created {$faresCreated} new fares!");
        } else {
            $this->command->warn('⚠️  No new fares were created. All fare pairs already exist.');
        }

        $this->command->newLine();
    }

    /**
     * Calculate base fare based on route stop sequence difference
     */
    private function calculateBaseFare(RouteStop $fromStop, RouteStop $toStop, int $sequenceDiff): float
    {
        // Base fare calculation based on sequence difference
        // More stops between = higher fare
        $baseFarePerStop = \fake()->randomFloat(2, 200, 500);
        $baseFare = $baseFarePerStop * $sequenceDiff;

        // Add some randomness to make it more realistic
        $variation = \fake()->randomFloat(2, 0.8, 1.2);
        $baseFare = $baseFare * $variation;

        // Ensure minimum fare
        $minFare = 300;
        $maxFare = 5000;

        return max($minFare, min($maxFare, round($baseFare, 2)));
    }

    /**
     * Calculate final fare based on discount
     */
    private function calculateFinalFare(float $baseFare, string $discountType, float $discountValue): float
    {
        if (! $discountType || ! $discountValue) {
            return $baseFare;
        }

        return match ($discountType) {
            'flat' => max(0, $baseFare - $discountValue),
            'percent' => max(0, $baseFare - ($baseFare * $discountValue / 100)),
            default => $baseFare,
        };
    }
}
