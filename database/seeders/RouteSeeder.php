<?php

namespace Database\Seeders;

use App\Enums\RouteStatusEnum;
use App\Models\City;
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

        // Get cities
        $cities = City::where('status', 'active')->get();

        if ($cities->isEmpty()) {
            $this->command->warn('No cities found. Please run CitySeeder first.');

            return;
        }

        // Get terminals for creating routes
        $terminals = Terminal::with('city')->where('status', 'active')->get();

        if ($terminals->isEmpty()) {
            $this->command->warn('No terminals found. Please run TerminalSeeder first.');

            return;
        }

        // Define routes using city names (will be matched to city codes)
        $routeDefinitions = [
            ['from' => 'Lahore', 'to' => 'Faisalabad'],
            ['from' => 'Faisalabad', 'to' => 'Lahore'],
            ['from' => 'Islamabad', 'to' => 'Lahore'],
            ['from' => 'Lahore', 'to' => 'Islamabad'],
            ['from' => 'Lahore', 'to' => 'Multan'],
            ['from' => 'Multan', 'to' => 'Lahore'],
            ['from' => 'Rawalpindi', 'to' => 'Lahore'],
            ['from' => 'Lahore', 'to' => 'Rawalpindi'],
            ['from' => 'Gujranwala', 'to' => 'Lahore'],
            ['from' => 'Lahore', 'to' => 'Gujranwala'],
        ];

        $createdRoutes = [];

        foreach ($routeDefinitions as $routeDef) {
            // Find cities by name (case-insensitive)
            $fromCity = $cities->first(function ($city) use ($routeDef) {
                return strcasecmp($city->name, $routeDef['from']) === 0;
            });

            $toCity = $cities->first(function ($city) use ($routeDef) {
                return strcasecmp($city->name, $routeDef['to']) === 0;
            });

            if (! $fromCity || ! $toCity) {
                $this->command->warn("Skipping route: {$routeDef['from']} to {$routeDef['to']} - cities not found");

                continue;
            }

            // Auto-generate route code and name from cities
            $baseRouteCode = $fromCity->code.'-'.$toCity->code;
            $routeCode = $baseRouteCode;
            $routeName = $fromCity->code.' → '.$toCity->code;

            // Check if route already exists by city IDs
            $existingRoute = Route::where('from_city_id', $fromCity->id)
                ->where('to_city_id', $toCity->id)
                ->first();

            if ($existingRoute) {
                $this->command->info("Route already exists: {$routeName} ({$routeCode})");
                $createdRoutes[] = $existingRoute;

                continue;
            }

            // Ensure route code uniqueness (append number if needed)
            $counter = 1;
            while (Route::where('code', $routeCode)->exists()) {
                $routeCode = $baseRouteCode.$counter;
                $counter++;
            }

            $route = Route::create([
                'from_city_id' => $fromCity->id,
                'to_city_id' => $toCity->id,
                'code' => $routeCode,
                'name' => $routeName,
                'direction' => 'forward',
                'base_currency' => 'PKR',
                'status' => RouteStatusEnum::ACTIVE->value,
            ]);

            $createdRoutes[] = $route;
            $this->command->info("Created route: {$routeName} ({$routeCode})");
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
        // Load city relationships
        $route->load('fromCity', 'toCity');

        if (! $route->fromCity || ! $route->toCity) {
            $this->command->warn("Route {$route->name} is missing city relationships");

            return;
        }

        // Get terminals for from and to cities
        $fromTerminals = $terminals->filter(function ($terminal) use ($route) {
            return $terminal->city_id === $route->from_city_id;
        });

        $toTerminals = $terminals->filter(function ($terminal) use ($route) {
            return $terminal->city_id === $route->to_city_id;
        });

        // Combine terminals: from city terminals first, then to city terminals
        $routeStops = $fromTerminals->merge($toTerminals);

        if ($routeStops->isEmpty()) {
            $this->command->warn("No terminals found for route: {$route->name}");

            return;
        }

        $sequence = 1;

        foreach ($routeStops as $terminal) {
            RouteStop::create([
                'route_id' => $route->id,
                'terminal_id' => $terminal->id,
                'sequence' => $sequence,
                'online_booking_allowed' => true,
            ]);

            $sequence++;
        }

        $this->command->info('Created '.($sequence - 1).' stops for route: '.$route->name);
    }
}
