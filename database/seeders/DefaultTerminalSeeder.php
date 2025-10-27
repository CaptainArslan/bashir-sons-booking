<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\Terminal;

class DefaultTerminalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $terminals = [
            ['code' => 'DRS', 'name' => 'Darbar'],
            ['code' => 'PIR', 'name' => 'Pirmahal'],
            ['code' => 'RAJ', 'name' => 'Rajanana'],
            ['code' => 'LHR', 'name' => 'Lahore'],
            ['code' => 'TTA', 'name' => 'Toba Tek Singh'],
            ['code' => 'SHR', 'name' => 'Shorkot'],
        ];

        foreach ($terminals as $data) {
            // Create or get the city
            $city = City::firstOrCreate(
                ['name' => $data['name']],
                ['status' => 'active']
            );

            // Create or update terminal
            Terminal::updateOrCreate(
                ['code' => $data['code']],
                [
                    'city_id' => $city->id,
                    'name' => $data['name'] . ' Terminal',
                    'address' => $data['name'] . ' Main Bus Stand',
                    'phone' => '000-0000000',
                    'email' => strtolower(str_replace(' ', '', $data['name'])) . '@terminal.com',
                    'landmark' => 'Near City Center',
                    'latitude' => null,
                    'longitude' => null,
                    'status' => 'active',
                ]
            );
        }
    }
}
