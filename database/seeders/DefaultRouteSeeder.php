<?php

namespace Database\Seeders;

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
        $routes = [
            [
                'code' => 'DRS-LHR-FWD',
                'name' => 'Darbar -> Lahore',
                'direction' => 'forward',
                'stops' => ['DRS', 'PIR', 'RAJ', 'LHR'],
            ],
            [
                'code' => 'LHR-DRS-RTN',
                'name' => 'Lahore -> Darbar',
                'direction' => 'return',
                'stops' => ['LHR', 'RAJ', 'PIR', 'DRS'],
            ],
            [
                'code' => 'PIR-LHR-FWD',
                'name' => 'Pirmahal -> Lahore',
                'direction' => 'forward',
                'stops' => ['PIR', 'RAJ', 'LHR'],
            ],
            [
                'code' => 'LHR-PIR-RTN',
                'name' => 'Lahore -> Pirmahal',
                'direction' => 'return',
                'stops' => ['LHR', 'RAJ', 'PIR'],
            ],
            [
                'code' => 'SHR-LHR-FWD',
                'name' => 'Shorkot -> Lahore',
                'direction' => 'forward',
                'stops' => ['SHR', 'TTA', 'RAJ', 'LHR'],
            ],
            [
                'code' => 'LHR-SHR-RTN',
                'name' => 'Lahore -> Shorkot',
                'direction' => 'return',
                'stops' => ['LHR', 'RAJ', 'TTA', 'SHR'],
            ],
            [
                'code' => 'LHR-TTA-FWD',
                'name' => 'Lahore -> Toba Tek Singh',
                'direction' => 'forward',
                'stops' => ['LHR', 'RAJ', 'TTA'],
            ],
            [
                'code' => 'TTA-LHR-RTN',
                'name' => 'Toba Tek Singh -> Lahore',
                'direction' => 'return',
                'stops' => ['TTA', 'RAJ', 'LHR'],
            ],
        ];

        foreach ($routes as $routeData) {
            // Create or update route
            $route = Route::updateOrCreate(
                ['code' => $routeData['code']],
                [
                    'operator_id' => 1, // Default operator, adjust if dynamic
                    'name' => $routeData['name'],
                    'direction' => $routeData['direction'],
                    'is_return_of' => null, // Can be set if you want -> link forward-return pairs
                    'base_currency' => 'PKR',
                    'status' => 'active',
                ]
            );

            // Clear existing stops -> reseed cleanly
            RouteStop::where('route_id', $route->id)->delete();

            foreach ($routeData['stops'] as $index => $terminalCode) {
                $terminal = Terminal::where('code', $terminalCode)->first();

                if (! $terminal) {
                    $this->command->warn("⚠️ Terminal with code {$terminalCode} not found. Skipping stop.");

                    continue;
                }

                RouteStop::create([
                    'route_id' => $route->id,
                    'terminal_id' => $terminal->id,
                    'sequence' => $index + 1,
                ]);
            }
        }
    }
}
