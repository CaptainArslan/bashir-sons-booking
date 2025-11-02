<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\Terminal;
use App\Enums\CityEnum;
use App\Enums\TerminalEnum;

class DefaultTerminalSeeder extends Seeder
{
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

            // ✅ Normalize city name to match City model mutator (lowercase with underscores)
            $normalizedCityName = strtolower(str_replace(' ', '_', $data['name']));

            // ✅ City safe seed - use normalized name for search/create
            $city = City::updateOrCreate(
                ['name' => $normalizedCityName],
                ['status' => CityEnum::ACTIVE->value]
            );

            // ✅ Terminal safe seed
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
                    'status' => TerminalEnum::ACTIVE->value,
                ]
            );
        }
    }
}
