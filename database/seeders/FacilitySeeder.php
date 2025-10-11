<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
                'icon' => 'fa-solid fa-snowflake',
            ],
            [
                'name' => 'WiFi',
                'description' => 'Free wireless internet connection available on board.',
                'icon' => 'fa-solid fa-wifi',
            ],
            [
                'name' => 'USB Charging',
                'description' => 'USB ports available at each seat for device charging.',
                'icon' => 'fa-solid fa-plug',
            ],
            [
                'name' => 'Reclining Seats',
                'description' => 'Comfortable reclining seats for long journeys.',
                'icon' => 'fa-solid fa-chair',
            ],
            [
                'name' => 'Entertainment System',
                'description' => 'Individual screens with movies and entertainment options.',
                'icon' => 'fa-solid fa-tv',
            ],
            [
                'name' => 'Restroom',
                'description' => 'Clean restroom facilities available on board.',
                'icon' => 'fa-solid fa-toilet',
            ],
            [
                'name' => 'Luggage Storage',
                'description' => 'Adequate overhead and under-seat storage for luggage.',
                'icon' => 'fa-solid fa-suitcase',
            ],
            [
                'name' => 'Snack Service',
                'description' => 'Complimentary snacks and beverages available.',
                'icon' => 'fa-solid fa-utensils',
            ],
            [
                'name' => 'Reading Light',
                'description' => 'Individual reading lights for each passenger.',
                'icon' => 'fa-solid fa-lightbulb',
            ],
            [
                'name' => 'Wheelchair Accessible',
                'description' => 'Fully accessible for passengers with mobility needs.',
                'icon' => 'fa-solid fa-wheelchair',
            ],
        ];

        foreach ($facilities as $facility) {
            Facility::firstOrCreate($facility);
        }
    }
}
