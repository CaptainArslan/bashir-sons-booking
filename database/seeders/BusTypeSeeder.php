<?php

namespace Database\Seeders;

use App\Models\BusType;
use App\Enums\BusTypeEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BusTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $busTypes = [
            [
                'name' => 'Standard Bus',
                'description' => 'Regular passenger bus with standard seating capacity and basic amenities.',
                'status' => BusTypeEnum::ACTIVE->value,
            ],
            [
                'name' => 'Luxury Bus',
                'description' => 'Premium bus with enhanced comfort features, reclining seats, and entertainment systems.',
                'status' => BusTypeEnum::ACTIVE->value,
            ],
            [
                'name' => 'Mini Bus',
                'description' => 'Smaller bus ideal for short routes and limited passenger capacity.',
                'status' => BusTypeEnum::ACTIVE->value,
            ],
            [
                'name' => 'Double Decker',
                'description' => 'Two-level bus providing increased passenger capacity for busy routes.',
                'status' => BusTypeEnum::ACTIVE->value,
            ],
            [
                'name' => 'Sleeper Bus',
                'description' => 'Long-distance bus with sleeping berths for overnight journeys.',
                'status' => BusTypeEnum::ACTIVE->value,
            ],
            [
                'name' => 'Semi-Sleeper',
                'description' => 'Bus with reclining seats that can be adjusted to a semi-sleeping position.',
                'status' => BusTypeEnum::ACTIVE->value,
            ],
            [
                'name' => 'AC Bus',
                'description' => 'Air-conditioned bus providing climate-controlled comfort for passengers.',
                'status' => BusTypeEnum::ACTIVE->value,
            ],
            [
                'name' => 'Non-AC Bus',
                'description' => 'Regular bus without air conditioning, suitable for shorter routes.',
                'status' => BusTypeEnum::ACTIVE->value,
            ],
            [
                'name' => 'Volvo Bus',
                'description' => 'Premium bus brand known for comfort, safety, and reliability.',
                'status' => BusTypeEnum::ACTIVE->value,
            ],
            [
                'name' => 'School Bus',
                'description' => 'Specially designed bus for transporting students with safety features.',
                'status' => BusTypeEnum::ACTIVE->value,
            ],
        ];

        foreach ($busTypes as $busType) {
            BusType::firstOrCreate($busType);
        }
    }
}
