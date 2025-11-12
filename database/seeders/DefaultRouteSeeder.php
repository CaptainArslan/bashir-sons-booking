<?php

namespace Database\Seeders;

use App\Enums\RouteStatusEnum;
use App\Models\City;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\Terminal;
use Illuminate\Database\Seeder;

class DefaultRouteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating default routes...');

        // Get all cities and terminals
        $cities = City::where('status', 'active')->get();
        $terminals = Terminal::with('city')->where('status', 'active')->get();

        if ($cities->isEmpty()) {
            $this->command->warn('No cities found. Please run CitySeeder first.');

            return;
        }

        if ($terminals->isEmpty()) {
            $this->command->warn('No terminals found. Please run TerminalSeeder first.');

            return;
        }

        // Define routes using terminal codes to find cities
        // First terminal code = from city, last terminal code = to city
        $routes = [
            [
                'direction' => 'forward',
                'terminal_codes' => ['DRS', 'PIR', 'RAJ', 'LHR'],
            ],
            [
                'direction' => 'return',
                'terminal_codes' => ['LHR', 'RAJ', 'PIR', 'DRS'],
            ],
            [
                'direction' => 'forward',
                'terminal_codes' => ['PIR', 'RAJ', 'LHR'],
            ],
            [
                'direction' => 'return',
                'terminal_codes' => ['LHR', 'RAJ', 'PIR'],
            ],
            [
                'direction' => 'forward',
                'terminal_codes' => ['SHR', 'TTA', 'RAJ', 'LHR'],
            ],
            [
                'direction' => 'return',
                'terminal_codes' => ['LHR', 'RAJ', 'TTA', 'SHR'],
            ],
            [
                'direction' => 'forward',
                'terminal_codes' => ['LHR', 'RAJ', 'TTA'],
            ],
            [
                'direction' => 'return',
                'terminal_codes' => ['TTA', 'RAJ', 'LHR'],
            ],
        ];

        foreach ($routes as $routeData) {
            $terminalCodes = $routeData['terminal_codes'];

            if (empty($terminalCodes)) {
                continue;
            }

            // Get terminals by codes
            $routeTerminals = collect($terminalCodes)->map(function ($code) use ($terminals) {
                return $terminals->firstWhere('code', $code);
            })->filter();

            if ($routeTerminals->isEmpty()) {
                $this->command->warn('No terminals found for route with codes: '.implode(', ', $terminalCodes));

                continue;
            }

            // Get from and to cities from first and last terminals
            $fromTerminal = $routeTerminals->first();
            $toTerminal = $routeTerminals->last();

            if (! $fromTerminal || ! $toTerminal || ! $fromTerminal->city || ! $toTerminal->city) {
                $this->command->warn('Missing city information for terminals: '.implode(', ', $terminalCodes));

                continue;
            }

            $fromCity = $fromTerminal->city;
            $toCity = $toTerminal->city;

            // Auto-generate route code and name from cities
            $baseRouteCode = $fromCity->code.'-'.$toCity->code;
            $routeCode = $baseRouteCode;
            $routeName = $fromCity->name.' → '.$toCity->name;

            // Check if route already exists by city IDs
            $existingRoute = Route::where('from_city_id', $fromCity->id)
                ->where('to_city_id', $toCity->id)
                ->first();

            if ($existingRoute) {
                $route = $existingRoute;
                $this->command->info("Route already exists: {$routeName} ({$routeCode})");
            } else {
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
                    'direction' => $routeData['direction'],
                    'base_currency' => 'PKR',
                    'status' => RouteStatusEnum::ACTIVE->value,
                ]);

                $this->command->info("Created route: {$routeName} ({$routeCode})");
            }

            // Clear existing stops and create new ones
            RouteStop::where('route_id', $route->id)->delete();

            $sequence = 1;
            foreach ($routeTerminals as $terminal) {
                RouteStop::create([
                    'route_id' => $route->id,
                    'terminal_id' => $terminal->id,
                    'sequence' => $sequence,
                    'online_booking_allowed' => true,
                ]);

                $sequence++;
            }

            $this->command->info('Created '.($sequence - 1)." stops for route: {$routeName}");
        }

        $this->command->info('Default route seeding completed!');
    }
}
