<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $facilities = [
            [
                'name' => 'Air Conditioning',
                'description' => 'Climate controlled environment for comfortable travel.',
                'icon' => 'bx bx-wind',
            ],
            [
                'name' => 'WiFi',
                'description' => 'Free wireless internet connection available on board.',
                'icon' => 'bx bx-wifi',
            ],
            [
                'name' => 'USB Charging',
                'description' => 'USB ports available at each seat for device charging.',
                'icon' => 'bx bx-plug',
            ],
            [
                'name' => 'Reclining Seats',
                'description' => 'Comfortable reclining seats for long journeys.',
                'icon' => 'bx bx-chair',
            ],
            [
                'name' => 'Entertainment System',
                'description' => 'Individual screens with movies and entertainment options.',
                'icon' => 'bx bx-tv',
            ],
            [
                'name' => 'Restroom',
                'description' => 'Clean restroom facilities available on board.',
                'icon' => 'bx bx-building-house',
            ],
            [
                'name' => 'Luggage Storage',
                'description' => 'Adequate overhead and under-seat storage for luggage.',
                'icon' => 'bx bx-briefcase',
            ],
            [
                'name' => 'Snack Service',
                'description' => 'Complimentary snacks and beverages available.',
                'icon' => 'bx bx-restaurant',
            ],
            [
                'name' => 'Reading Light',
                'description' => 'Individual reading lights for each passenger.',
                'icon' => 'bx bx-bulb',
            ],
            [
                'name' => 'Wheelchair Accessible',
                'description' => 'Fully accessible for passengers with mobility needs.',
                'icon' => 'bx bx-chair',
            ],
        ];

        foreach ($facilities as $facility) {
            Facility::firstOrCreate($facility);
        }
    }
}
