<?php

namespace Database\Seeders;

use App\Models\Terminal;
use App\Models\City;
use App\Enums\TerminalEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TerminalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all cities or create some if none exist
        $cities = City::all();
        
        if ($cities->isEmpty()) {
            $this->command->info('No cities found. Creating sample cities first...');
            $cities = collect([
                ['name' => 'Karachi', 'status' => 'active'],
                ['name' => 'Lahore', 'status' => 'active'],
                ['name' => 'Islamabad', 'status' => 'active'],
                ['name' => 'Rawalpindi', 'status' => 'active'],
                ['name' => 'Faisalabad', 'status' => 'active'],
                ['name' => 'Multan', 'status' => 'active'],
                ['name' => 'Peshawar', 'status' => 'active'],
                ['name' => 'Quetta', 'status' => 'active'],
            ]);
            
            foreach ($cities as $cityData) {
                City::create($cityData);
            }
            
            $cities = City::all();
        }

        $this->command->info('Creating terminals for ' . $cities->count() . ' cities...');

        // Create terminals for each city
        foreach ($cities as $city) {
            $this->command->info("Creating terminals for {$city->name}...");
            
            // Create 2-4 terminals per city
            $terminalCount = rand(2, 4);
            
            for ($i = 0; $i < $terminalCount; $i++) {
                // Generate unique terminal name
                $terminalName = $this->generateUniqueTerminalName($city->name, $i + 1);
                
                Terminal::factory()
                    ->active() // Most terminals should be active
                    ->withLocation() // Include GPS coordinates
                    ->withContact() // Include email and landmark
                    ->create([
                        'city_id' => $city->id,
                        'name' => $terminalName,
                        'address' => $this->generateAddress($city->name),
                        'phone' => $this->generatePhoneNumber(),
                        'email' => $this->generateEmail($city->name),
                        'landmark' => $this->generateLandmark(),
                        'latitude' => $this->generateLatitude(),
                        'longitude' => $this->generateLongitude(),
                    ]);
            }
        }

        // Create some inactive terminals (10% of total)
        $totalTerminals = Terminal::count();
        $inactiveCount = max(1, intval($totalTerminals * 0.1));
        
        $this->command->info("Creating {$inactiveCount} inactive terminals...");
        
        Terminal::factory($inactiveCount)
            ->inactive()
            ->create();

        $this->command->info('Terminal seeding completed!');
        $this->command->info('Total terminals created: ' . Terminal::count());
    }

    /**
     * Generate unique terminal name based on city
     */
    private function generateUniqueTerminalName(string $cityName, int $index): string
    {
        $prefixes = ['Central', 'Main', 'City', 'Express', 'North', 'South', 'East', 'West', 'Downtown', 'Airport', 'Union', 'Liberty', 'Grand', 'Plaza', 'Gateway', 'Crossroads', 'Junction', 'Hub'];
        $suffixes = ['Terminal', 'Bus Station', 'Terminal Complex', 'Transport Hub', 'Bus Depot', 'Transit Center'];
        
        if ($index === 1) {
            $baseName = "Central Terminal {$cityName}";
        } else {
            $baseName = $prefixes[array_rand($prefixes)] . ' ' . $suffixes[array_rand($suffixes)] . ' ' . $cityName;
        }
        
        // Check if name already exists and add unique identifier if needed
        $terminalName = $baseName;
        $counter = 1;
        
        while (Terminal::where('name', $terminalName)->exists()) {
            $terminalName = $baseName . ' ' . $counter;
            $counter++;
        }
        
        return $terminalName;
    }

    /**
     * Generate realistic address
     */
    private function generateAddress(string $cityName): string
    {
        $streets = ['Main Road', 'Station Road', 'Highway 1', 'Commercial Street', 'Business Avenue', 'Transport Road'];
        $areas = ['City Center', 'Downtown', 'Commercial Area', 'Business District', 'Transport Hub', 'Central Plaza'];
        
        return $streets[array_rand($streets)] . ', ' . $areas[array_rand($areas)] . ', ' . $cityName;
    }

    /**
     * Generate phone number
     */
    private function generatePhoneNumber(): string
    {
        $prefixes = ['0300', '0310', '0320', '0330', '0340', '0350', '0360', '0370'];
        return $prefixes[array_rand($prefixes)] . '-' . rand(1000000, 9999999);
    }

    /**
     * Generate email
     */
    private function generateEmail(string $cityName): string
    {
        $domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com'];
        $username = strtolower(str_replace(' ', '', $cityName)) . 'terminal' . rand(1, 99);
        return $username . '@' . $domains[array_rand($domains)];
    }

    /**
     * Generate landmark
     */
    private function generateLandmark(): string
    {
        $landmarks = [
            'Near Shopping Mall', 'Opposite Railway Station', 'Next to Hospital',
            'Behind City Hall', 'Near Airport', 'Close to University', 'Next to Park',
            'Opposite Bank', 'Near Mosque', 'Close to Market', 'Next to School',
            'Behind Police Station', 'Near Hotel', 'Opposite Restaurant'
        ];
        
        return $landmarks[array_rand($landmarks)];
    }

    /**
     * Generate latitude for Pakistan
     */
    private function generateLatitude(): string
    {
        return (string) rand(24000000, 37000000) / 1000000; // Pakistan latitude range
    }

    /**
     * Generate longitude for Pakistan
     */
    private function generateLongitude(): string
    {
        return (string) rand(61000000, 78000000) / 1000000; // Pakistan longitude range
    }
}
