<?php

namespace Database\Seeders;

use App\Enums\BusEnum;
use App\Models\Bus;
use App\Models\BusType;
use App\Models\Facility;
use Illuminate\Database\Seeder;

class BusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing bus types and facilities
        $busTypes = BusType::all();
        $facilities = Facility::all();

        $buses = [
            [
                'name' => 'City Express 001',
                'description' => 'Modern city bus for urban transportation with comfortable seating and air conditioning.',
                'bus_type_id' => $busTypes->where('name', 'Standard Bus')->first()?->id ?? 1,
                'total_seats' => 45,
                'registration_number' => 'BS-001-2024',
                'model' => 'Mercedes Citaro',
                'color' => 'Blue',
                'status' => BusEnum::ACTIVE->value,
            ],
            [
                'name' => 'Luxury Coach 002',
                'description' => 'Premium luxury coach for long-distance travel with entertainment system and reclining seats.',
                'bus_type_id' => $busTypes->where('name', 'Luxury Bus')->first()?->id ?? 2,
                'total_seats' => 35,
                'registration_number' => 'BS-002-2024',
                'model' => 'Volvo 9700',
                'color' => 'White',
                'status' => BusEnum::ACTIVE->value,
            ],
            [
                'name' => 'Mini Shuttle 003',
                'description' => 'Compact mini bus perfect for short routes and airport transfers.',
                'bus_type_id' => $busTypes->where('name', 'Mini Bus')->first()?->id ?? 3,
                'total_seats' => 25,
                'registration_number' => 'BS-003-2024',
                'model' => 'Toyota Coaster',
                'color' => 'Green',
                'status' => BusEnum::ACTIVE->value,
            ],
            [
                'name' => 'Night Sleeper 004',
                'description' => 'Overnight sleeper bus with comfortable berths for long-distance night journeys.',
                'bus_type_id' => $busTypes->where('name', 'Sleeper Bus')->first()?->id ?? 4,
                'total_seats' => 30,
                'registration_number' => 'BS-004-2024',
                'model' => 'Scania K410',
                'color' => 'Red',
                'status' => BusEnum::ACTIVE->value,
            ],
            [
                'name' => 'Semi-Sleeper 005',
                'description' => 'Semi-sleeper bus with reclining seats for comfortable long-distance travel.',
                'bus_type_id' => $busTypes->where('name', 'Semi-Sleeper')->first()?->id ?? 5,
                'total_seats' => 40,
                'registration_number' => 'BS-005-2024',
                'model' => 'Ashok Leyland',
                'color' => 'Silver',
                'status' => BusEnum::ACTIVE->value,
            ],
            [
                'name' => 'Double Decker 006',
                'description' => 'Two-level bus providing panoramic views and increased passenger capacity.',
                'bus_type_id' => $busTypes->where('name', 'Double Decker')->first()?->id ?? 6,
                'total_seats' => 80,
                'registration_number' => 'BS-006-2024',
                'model' => 'Alexander Dennis Enviro500',
                'color' => 'Yellow',
                'status' => BusEnum::ACTIVE->value,
            ],
            [
                'name' => 'School Transport 007',
                'description' => 'Specialized school bus with safety features for student transportation.',
                'bus_type_id' => $busTypes->where('name', 'School Bus')->first()?->id ?? 7,
                'total_seats' => 50,
                'registration_number' => 'BS-007-2024',
                'model' => 'International CE',
                'color' => 'Orange',
                'status' => BusEnum::ACTIVE->value,
            ],
            [
                'name' => 'Executive VIP 008',
                'description' => 'Premium executive bus for VIP transportation with maximum comfort and privacy.',
                'bus_type_id' => $busTypes->where('name', 'Luxury Bus')->first()?->id ?? 2,
                'total_seats' => 20,
                'registration_number' => 'BS-008-2024',
                'model' => 'Mercedes Sprinter',
                'color' => 'Black',
                'status' => BusEnum::ACTIVE->value,
            ],
            [
                'name' => 'High Capacity 009',
                'description' => 'High-capacity bus designed for maximum passenger load on busy routes.',
                'bus_type_id' => $busTypes->where('name', 'Standard Bus')->first()?->id ?? 1,
                'total_seats' => 55,
                'registration_number' => 'BS-009-2024',
                'model' => 'Volvo B7R',
                'color' => 'Blue',
                'status' => BusEnum::ACTIVE->value,
            ],
            [
                'name' => 'AC Express 010',
                'description' => 'Air-conditioned express bus for comfortable city and intercity travel.',
                'bus_type_id' => $busTypes->where('name', 'AC Bus')->first()?->id ?? 8,
                'total_seats' => 45,
                'registration_number' => 'BS-010-2024',
                'model' => 'Tata Starbus',
                'color' => 'White',
                'status' => BusEnum::ACTIVE->value,
            ],
        ];

        foreach ($buses as $busData) {
            // Use updateOrCreate with name as unique identifier
            $bus = Bus::updateOrCreate(
                ['name' => $busData['name']],
                $busData
            );

            // Attach common facilities to each bus
            if ($facilities->isNotEmpty()) {
                $randomFacilities = $facilities->random(rand(3, 6));
                $bus->facilities()->sync($randomFacilities->pluck('id'));
            }
        }

        $this->command->info('Bus seeding completed!');
        $this->command->info('Total buses created: '.Bus::count());
    }
}
