<?php

namespace Database\Seeders;

use App\Enums\RouteStatusEnum;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\Terminal;
use Illuminate\Database\Seeder;

class RouteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating sample routes...');

        // Get some terminals for creating routes
        $terminals = Terminal::with('city')->where('status', 'active')->get();

        if ($terminals->isEmpty()) {
            $this->command->warn('No terminals found. Please run TerminalSeeder first.');

            return;
        }

        // Create sample routes
        $routes = [
            [
                'name' => 'Karachi to Lahore Express',
                'code' => 'KAR-LAH-001',
                'direction' => 'forward',
                'base_currency' => 'PKR',
                'status' => RouteStatusEnum::ACTIVE->value,
            ],
            [
                'name' => 'Lahore to Karachi Express',
                'code' => 'LAH-KAR-001',
                'direction' => 'return',
                'base_currency' => 'PKR',
                'status' => RouteStatusEnum::ACTIVE->value,
            ],
            [
                'name' => 'Islamabad to Karachi',
                'code' => 'ISL-KAR-001',
                'direction' => 'forward',
                'base_currency' => 'PKR',
                'status' => RouteStatusEnum::ACTIVE->value,
            ],
            [
                'name' => 'Karachi to Islamabad',
                'code' => 'KAR-ISL-001',
                'direction' => 'return',
                'base_currency' => 'PKR',
                'status' => RouteStatusEnum::ACTIVE->value,
            ],
            [
                'name' => 'Lahore to Peshawar',
                'code' => 'LAH-PES-001',
                'direction' => 'forward',
                'base_currency' => 'PKR',
                'status' => RouteStatusEnum::ACTIVE->value,
            ],
            [
                'name' => 'Peshawar to Lahore',
                'code' => 'PES-LAH-001',
                'direction' => 'return',
                'base_currency' => 'PKR',
                'status' => RouteStatusEnum::ACTIVE->value,
            ],
        ];

        $createdRoutes = [];

        foreach ($routes as $routeData) {
            $route = Route::create($routeData);
            $createdRoutes[] = $route;
            $this->command->info("Created route: {$route->name} ({$route->code})");
        }

        // Create route stops for each route
        $this->command->info('Creating route stops...');

        foreach ($createdRoutes as $route) {
            $this->createRouteStops($route, $terminals);
        }

        $this->command->info('Route seeding completed!');
        $this->command->info('Total routes created: '.Route::count());
        $this->command->info('Total route stops created: '.RouteStop::count());
    }

    /**
     * Create route stops for a given route
     */
    private function createRouteStops(Route $route, $terminals)
    {
        // Get terminals based on route direction and cities
        $routeStops = $this->getRouteStopsForRoute($route, $terminals);

        if (empty($routeStops)) {
            $this->command->warn("No suitable terminals found for route: {$route->name}");

            return;
        }

        $sequence = 1;

        foreach ($routeStops as $terminal) {
            RouteStop::create([
                'route_id' => $route->id,
                'terminal_id' => $terminal->id,
                'sequence' => $sequence,
            ]);

            $sequence++;
        }

        $this->command->info('Created '.($sequence - 1)." stops for route: {$route->name}");
    }

    /**
     * Get terminals for a specific route based on route name and direction
     */
    private function getRouteStopsForRoute(Route $route, $terminals)
    {
        $routeName = strtolower($route->name);
        $stops = [];

        // Define route patterns
        if (strpos($routeName, 'karachi to lahore') !== false) {
            $stops = $terminals->filter(function ($terminal) {
                return in_array(strtolower($terminal->city->name), ['karachi', 'lahore']);
            })->sortBy(function ($terminal) {
                return $terminal->city->name === 'Karachi' ? 1 : 2;
            });
        } elseif (strpos($routeName, 'lahore to karachi') !== false) {
            $stops = $terminals->filter(function ($terminal) {
                return in_array(strtolower($terminal->city->name), ['karachi', 'lahore']);
            })->sortBy(function ($terminal) {
                return $terminal->city->name === 'Lahore' ? 1 : 2;
            });
        } elseif (strpos($routeName, 'islamabad to karachi') !== false) {
            $stops = $terminals->filter(function ($terminal) {
                return in_array(strtolower($terminal->city->name), ['islamabad', 'karachi']);
            })->sortBy(function ($terminal) {
                return $terminal->city->name === 'Islamabad' ? 1 : 2;
            });
        } elseif (strpos($routeName, 'karachi to islamabad') !== false) {
            $stops = $terminals->filter(function ($terminal) {
                return in_array(strtolower($terminal->city->name), ['karachi', 'islamabad']);
            })->sortBy(function ($terminal) {
                return $terminal->city->name === 'Karachi' ? 1 : 2;
            });
        } elseif (strpos($routeName, 'lahore to peshawar') !== false) {
            $stops = $terminals->filter(function ($terminal) {
                return in_array(strtolower($terminal->city->name), ['lahore', 'peshawar']);
            })->sortBy(function ($terminal) {
                return $terminal->city->name === 'Lahore' ? 1 : 2;
            });
        } elseif (strpos($routeName, 'peshawar to lahore') !== false) {
            $stops = $terminals->filter(function ($terminal) {
                return in_array(strtolower($terminal->city->name), ['peshawar', 'lahore']);
            })->sortBy(function ($terminal) {
                return $terminal->city->name === 'Peshawar' ? 1 : 2;
            });
        }

        // If no specific pattern found, create a generic route with first 3 terminals
        if (empty($stops)) {
            $stops = $terminals->take(3);
        }

        return $stops->values();
    }
}
