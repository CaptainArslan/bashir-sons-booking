<?php

namespace App\Console\Commands;

use App\Enums\TerminalEnum;
use App\Models\City;
use App\Models\Terminal;
use Illuminate\Console\Command;

class CreateSampleTerminalsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'terminals:sample';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create 5 sample terminals with predefined data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚌 Creating 5 sample terminals...');
        $this->newLine();

        // Get or create cities
        $cities = $this->getOrCreateCities();

        // Sample terminals data
        $sampleTerminals = [
            [
                'city' => 'Karachi',
                'name' => 'Karachi Central Terminal',
                'code' => 'KCT',
                'address' => 'Main Road, Saddar, Karachi',
                'phone' => '+92-21-1234567',
                'email' => 'kct@bashirsons.com',
                'landmark' => 'Near Saddar Market',
            ],
            [
                'city' => 'Lahore',
                'name' => 'Lahore Main Bus Station',
                'code' => 'LBS',
                'address' => 'Railway Station Road, Lahore',
                'phone' => '+92-42-2345678',
                'email' => 'lbs@bashirsons.com',
                'landmark' => 'Near Lahore Railway Station',
            ],
            [
                'city' => 'Islamabad',
                'name' => 'Islamabad Express Terminal',
                'code' => 'IET',
                'address' => 'Blue Area, Islamabad',
                'phone' => '+92-51-3456789',
                'email' => 'iet@bashirsons.com',
                'landmark' => 'Near Faisal Mosque',
            ],
            [
                'city' => 'Rawalpindi',
                'name' => 'Rawalpindi City Terminal',
                'code' => 'RCT',
                'address' => 'Mall Road, Rawalpindi',
                'phone' => '+92-51-4567890',
                'email' => 'rct@bashirsons.com',
                'landmark' => 'Near Rawalpindi Cantt',
            ],
            [
                'city' => 'Multan',
                'name' => 'Multan Intercity Terminal',
                'code' => 'MIT',
                'address' => 'Highway Road, Multan',
                'phone' => '+92-61-5678901',
                'email' => 'mit@bashirsons.com',
                'landmark' => 'Near Multan Airport',
            ],
        ];

        $terminals = [];

        foreach ($sampleTerminals as $index => $terminalData) {
            $city = $cities->firstWhere('name', $terminalData['city']);

            if (! $city) {
                $this->error("❌ City '{$terminalData['city']}' not found!");

                continue;
            }

            $terminals[] = [
                'city_id' => $city->id,
                'name' => $terminalData['name'],
                'code' => $terminalData['code'],
                'address' => $terminalData['address'],
                'phone' => $terminalData['phone'],
                'email' => $terminalData['email'],
                'landmark' => $terminalData['landmark'],
                'latitude' => null,
                'longitude' => null,
                'status' => TerminalEnum::ACTIVE,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $this->info('✅ Terminal #'.($index + 1).": {$terminalData['name']} - {$terminalData['city']}");
        }

        if (empty($terminals)) {
            $this->error('❌ No terminals to create!');

            return 1;
        }

        try {
            Terminal::insert($terminals);

            $this->newLine();
            $this->info('🎉 Successfully created '.count($terminals).' sample terminals!');
            $this->newLine();

            // Show created terminals in a table
            $this->table(
                ['#', 'Name', 'Code', 'City', 'Address', 'Phone'],
                collect($terminals)->map(function ($terminal, $index) use ($cities) {
                    $city = $cities->firstWhere('id', $terminal['city_id']);

                    return [
                        $index + 1,
                        $terminal['name'],
                        $terminal['code'],
                        $city->name,
                        $terminal['address'],
                        $terminal['phone'],
                    ];
                })
            );

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error creating terminals: '.$e->getMessage());

            return 1;
        }
    }

    /**
     * Get or create cities needed for terminals
     */
    private function getOrCreateCities()
    {
        $requiredCities = ['Karachi', 'Lahore', 'Islamabad', 'Rawalpindi', 'Multan'];
        $cities = collect();

        foreach ($requiredCities as $cityName) {
            $city = City::where('name', $cityName)->first();

            if (! $city) {
                $city = City::create([
                    'name' => $cityName,
                    'status' => 'active',
                ]);
                $this->info("📍 Created city: {$cityName}");
            }

            $cities->push($city);
        }

        return $cities;
    }
}
