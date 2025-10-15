<?php

namespace Database\Seeders;

use App\Models\Route;
use App\Models\RouteStop;
use App\Models\RouteFare;
use App\Enums\RouteFareStatusEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RouteFareSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating route fares...');

        // Get all routes with their stops
        $routes = Route::with('routeStops.terminal.city')->get();

        foreach ($routes as $route) {
            $stops = $route->routeStops->sortBy('sequence');
            
            if ($stops->count() < 2) {
                continue; // Skip routes with less than 2 stops
            }

            $this->command->info("Creating fares for route: {$route->name}");

            // Create fares between all stops
            for ($i = 0; $i < $stops->count(); $i++) {
                for ($j = $i + 1; $j < $stops->count(); $j++) {
                    $fromStop = $stops[$i];
                    $toStop = $stops[$j];

                    // Calculate base fare based on distance and stops
                    $baseFare = $this->calculateBaseFare($fromStop, $toStop);
                    
                    // Randomly decide if there's a discount
                    $hasDiscount = rand(1, 10) <= 3; // 30% chance of discount
                    
                    $discountType = null;
                    $discountValue = null;
                    
                    if ($hasDiscount) {
                        $discountType = rand(1, 2) === 1 ? 'flat' : 'percent';
                        
                        if ($discountType === 'flat') {
                            $discountValue = rand(50, min(500, $baseFare * 0.3)); // Flat discount up to 30% of base fare
                        } else {
                            $discountValue = rand(5, 25); // Percentage discount 5-25%
                        }
                    }

                    // Calculate final fare
                    $finalFare = $this->calculateFinalFare($baseFare, $discountType, $discountValue);

                    RouteFare::create([
                        'route_id' => $route->id,
                        'from_stop_id' => $fromStop->id,
                        'to_stop_id' => $toStop->id,
                        'base_fare' => $baseFare,
                        'discount_type' => $discountType,
                        'discount_value' => $discountValue,
                        'final_fare' => $finalFare,
                        'status' => RouteFareStatusEnum::ACTIVE->value,
                    ]);

                    $this->command->info("  Created fare: {$fromStop->terminal->name} → {$toStop->terminal->name} (PKR {$finalFare})");
                }
            }
        }

        $this->command->info('Route fares created successfully!');
    }

    /**
     * Calculate base fare based on distance and number of stops
     */
    private function calculateBaseFare(RouteStop $fromStop, RouteStop $toStop): float
    {
        // Base fare calculation factors
        $baseRate = 50; // PKR per km
        $stopRate = 20; // PKR per stop
        
        // Calculate total distance
        $totalDistance = 0;
        $stops = RouteStop::where('route_id', $fromStop->route_id)
            ->whereBetween('sequence', [$fromStop->sequence, $toStop->sequence])
            ->orderBy('sequence')
            ->get();
            
        foreach ($stops as $stop) {
            if ($stop->distance_from_previous) {
                $totalDistance += $stop->distance_from_previous;
            }
        }
        
        // If no distance data, estimate based on stops
        if ($totalDistance === 0) {
            $totalDistance = ($toStop->sequence - $fromStop->sequence) * 50; // Assume 50km per stop
        }
        
        // Calculate fare
        $distanceFare = $totalDistance * $baseRate;
        $stopFare = ($toStop->sequence - $fromStop->sequence) * $stopRate;
        
        return round($distanceFare + $stopFare, 2);
    }

    /**
     * Calculate final fare with discount
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