<?php

namespace Database\Seeders;

use App\Models\BusLayout;
use App\Enums\BusLayoutEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BusLayoutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $busLayouts = [
            [
                'name' => 'Standard 2+2 Layout',
                'description' => 'Standard bus layout with 2 seats on each side of the aisle, suitable for regular passenger buses.',
                'total_rows' => 12,
                'total_columns' => 4,
                'total_seats' => 48,
                'seat_map' => [
                    // 'layout' => [
                    //     [1, 2, null, 3, 4],
                    //     [5, 6, null, 7, 8],
                    //     [9, 10, null, 11, 12],
                    //     [13, 14, null, 15, 16],
                    //     [17, 18, null, 19, 20],
                    //     [21, 22, null, 23, 24],
                    //     [25, 26, null, 27, 28],
                    //     [29, 30, null, 31, 32],
                    //     [33, 34, null, 35, 36],
                    //     [37, 38, null, 39, 40],
                    //     [41, 42, null, 43, 44],
                    //     [45, 46, null, 47, 48]
                    // ],
                    // 'aisle_position' => 2
                ],
                'status' => BusLayoutEnum::ACTIVE->value,
            ],
            [
                'name' => 'Luxury 2+1 Layout',
                'description' => 'Premium layout with 2 seats on one side and 1 seat on the other, providing extra comfort.',
                'total_rows' => 10,
                'total_columns' => 3,
                'total_seats' => 30,
                'seat_map' => [
                    'layout' => [
                        [1, 2, null, 3],
                        [4, 5, null, 6],
                        [7, 8, null, 9],
                        [10, 11, null, 12],
                        [13, 14, null, 15],
                        [16, 17, null, 18],
                        [19, 20, null, 21],
                        [22, 23, null, 24],
                        [25, 26, null, 27],
                        [28, 29, null, 30]
                    ],
                    'aisle_position' => 2
                ],
                'status' => BusLayoutEnum::ACTIVE->value,
            ],
            [
                'name' => 'Mini Bus Layout',
                'description' => 'Compact layout for mini buses with limited seating capacity.',
                'total_rows' => 8,
                'total_columns' => 3,
                'total_seats' => 24,
                'seat_map' => [
                    'layout' => [
                        [1, 2, null, 3],
                        [4, 5, null, 6],
                        [7, 8, null, 9],
                        [10, 11, null, 12],
                        [13, 14, null, 15],
                        [16, 17, null, 18],
                        [19, 20, null, 21],
                        [22, 23, null, 24]
                    ],
                    'aisle_position' => 2
                ],
                'status' => BusLayoutEnum::ACTIVE->value,
            ],
            [
                'name' => 'Sleeper Bus Layout',
                'description' => 'Layout designed for overnight journeys with sleeping berths instead of regular seats.',
                'total_rows' => 8,
                'total_columns' => 2,
                'total_seats' => 16,
                'seat_map' => [
                    'layout' => [
                        [1, null, 2],
                        [3, null, 4],
                        [5, null, 6],
                        [7, null, 8],
                        [9, null, 10],
                        [11, null, 12],
                        [13, null, 14],
                        [15, null, 16]
                    ],
                    'aisle_position' => 1,
                    'berth_type' => 'horizontal'
                ],
                'status' => BusLayoutEnum::ACTIVE->value,
            ],
            [
                'name' => 'Semi-Sleeper Layout',
                'description' => 'Layout with reclining seats that can be adjusted to a semi-sleeping position.',
                'total_rows' => 11,
                'total_columns' => 3,
                'total_seats' => 33,
                'seat_map' => [
                    'layout' => [
                        [1, 2, null, 3],
                        [4, 5, null, 6],
                        [7, 8, null, 9],
                        [10, 11, null, 12],
                        [13, 14, null, 15],
                        [16, 17, null, 18],
                        [19, 20, null, 21],
                        [22, 23, null, 24],
                        [25, 26, null, 27],
                        [28, 29, null, 30],
                        [31, 32, null, 33]
                    ],
                    'aisle_position' => 2,
                    'reclining_seats' => true
                ],
                'status' => BusLayoutEnum::ACTIVE->value,
            ],
            [
                'name' => 'Double Decker Upper Deck',
                'description' => 'Upper deck layout for double decker buses with panoramic views.',
                'total_rows' => 10,
                'total_columns' => 4,
                'total_seats' => 40,
                'seat_map' => [
                    'layout' => [
                        [1, 2, null, 3, 4],
                        [5, 6, null, 7, 8],
                        [9, 10, null, 11, 12],
                        [13, 14, null, 15, 16],
                        [17, 18, null, 19, 20],
                        [21, 22, null, 23, 24],
                        [25, 26, null, 27, 28],
                        [29, 30, null, 31, 32],
                        [33, 34, null, 35, 36],
                        [37, 38, null, 39, 40]
                    ],
                    'aisle_position' => 2,
                    'deck' => 'upper'
                ],
                'status' => BusLayoutEnum::ACTIVE->value,
            ],
            [
                'name' => 'Double Decker Lower Deck',
                'description' => 'Lower deck layout for double decker buses with standard seating.',
                'total_rows' => 8,
                'total_columns' => 4,
                'total_seats' => 32,
                'seat_map' => [
                    'layout' => [
                        [1, 2, null, 3, 4],
                        [5, 6, null, 7, 8],
                        [9, 10, null, 11, 12],
                        [13, 14, null, 15, 16],
                        [17, 18, null, 19, 20],
                        [21, 22, null, 23, 24],
                        [25, 26, null, 27, 28],
                        [29, 30, null, 31, 32]
                    ],
                    'aisle_position' => 2,
                    'deck' => 'lower'
                ],
                'status' => BusLayoutEnum::ACTIVE->value,
            ],
            [
                'name' => 'School Bus Layout',
                'description' => 'Layout designed for student transportation with safety features and high capacity.',
                'total_rows' => 13,
                'total_columns' => 3,
                'total_seats' => 39,
                'seat_map' => [
                    'layout' => [
                        [1, 2, null, 3],
                        [4, 5, null, 6],
                        [7, 8, null, 9],
                        [10, 11, null, 12],
                        [13, 14, null, 15],
                        [16, 17, null, 18],
                        [19, 20, null, 21],
                        [22, 23, null, 24],
                        [25, 26, null, 27],
                        [28, 29, null, 30],
                        [31, 32, null, 33],
                        [34, 35, null, 36],
                        [37, 38, null, 39]
                    ],
                    'aisle_position' => 2,
                    'student_seating' => true
                ],
                'status' => BusLayoutEnum::ACTIVE->value,
            ],
            [
                'name' => 'Executive Bus Layout',
                'description' => 'Premium layout for executive transportation with maximum comfort and privacy.',
                'total_rows' => 6,
                'total_columns' => 2,
                'total_seats' => 12,
                'seat_map' => [
                    'layout' => [
                        [1, null, 2],
                        [3, null, 4],
                        [5, null, 6],
                        [7, null, 8],
                        [9, null, 10],
                        [11, null, 12]
                    ],
                    'aisle_position' => 1,
                    'executive_seating' => true
                ],
                'status' => BusLayoutEnum::ACTIVE->value,
            ],
            [
                'name' => 'High Capacity Layout',
                'description' => 'Layout designed for maximum passenger capacity with 3+2 seating arrangement.',
                'total_rows' => 12,
                'total_columns' => 5,
                'total_seats' => 60,
                'seat_map' => [
                    'layout' => [
                        [1, 2, 3, null, 4, 5],
                        [6, 7, 8, null, 9, 10],
                        [11, 12, 13, null, 14, 15],
                        [16, 17, 18, null, 19, 20],
                        [21, 22, 23, null, 24, 25],
                        [26, 27, 28, null, 29, 30],
                        [31, 32, 33, null, 34, 35],
                        [36, 37, 38, null, 39, 40],
                        [41, 42, 43, null, 44, 45],
                        [46, 47, 48, null, 49, 50],
                        [51, 52, 53, null, 54, 55],
                        [56, 57, 58, null, 59, 60]
                    ],
                    'aisle_position' => 3,
                    'high_capacity' => true
                ],
                'status' => BusLayoutEnum::ACTIVE->value,
            ],
        ];

        foreach ($busLayouts as $busLayout) {
            BusLayout::firstOrCreate($busLayout);
        }
    }
}
