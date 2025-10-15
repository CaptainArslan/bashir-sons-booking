<?php

namespace Database\Factories;

use App\Models\Terminal;
use App\Models\City;
use App\Enums\TerminalEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Terminal>
 */
class TerminalFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Terminal::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Get random city or create one if none exists
        $city = City::inRandomOrder()->first();
        if (!$city) {
            $city = City::factory()->create();
        }

        // Generate realistic terminal names with unique identifiers
        $terminalNames = [
            'Central Terminal', 'Main Bus Station', 'City Terminal', 'Express Terminal',
            'North Terminal', 'South Terminal', 'East Terminal', 'West Terminal',
            'Downtown Terminal', 'Airport Terminal', 'Railway Terminal', 'Metro Terminal',
            'Intercity Terminal', 'Regional Terminal', 'Commercial Terminal', 'Public Terminal',
            'Union Terminal', 'Liberty Terminal', 'Grand Terminal', 'Plaza Terminal',
            'Gateway Terminal', 'Crossroads Terminal', 'Junction Terminal', 'Hub Terminal'
        ];

        // Generate realistic addresses
        $addresses = [
            'Main Road, City Center', 'Highway 1, Near Mall', 'Station Road, Downtown',
            'Commercial Area, Block A', 'Business District, Sector 5', 'Transport Hub, Zone 2',
            'Central Plaza, Ground Floor', 'City Square, Terminal Building', 'Main Street, Terminal Complex',
            'Highway Junction, Terminal Point', 'Industrial Area, Terminal Block', 'Residential Area, Terminal Station'
        ];

        // Generate realistic landmarks
        $landmarks = [
            'Near Shopping Mall', 'Opposite Railway Station', 'Next to Hospital',
            'Behind City Hall', 'Near Airport', 'Close to University', 'Next to Park',
            'Opposite Bank', 'Near Mosque', 'Close to Market', 'Next to School',
            'Behind Police Station', 'Near Hotel', 'Opposite Restaurant'
        ];

        return [
            'city_id' => $city->id,
            'name' => $this->faker->randomElement($terminalNames) . ' ' . $city->name . ' ' . $this->faker->unique()->numberBetween(1, 999),
            'code' => $this->generateTerminalCode($city->name),
            'address' => $this->faker->randomElement($addresses),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->optional(0.7)->safeEmail(), // 70% chance of having email
            'landmark' => $this->faker->optional(0.8)->randomElement($landmarks), // 80% chance of having landmark
            'latitude' => $this->faker->optional(0.6)->latitude(24, 37), // Pakistan latitude range
            'longitude' => $this->faker->optional(0.6)->longitude(61, 78), // Pakistan longitude range
            'status' => $this->faker->randomElement(TerminalEnum::getStatuses()),
        ];
    }

    /**
     * Indicate that the terminal is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TerminalEnum::ACTIVE->value,
        ]);
    }

    /**
     * Indicate that the terminal is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TerminalEnum::INACTIVE->value,
        ]);
    }

    /**
     * Indicate that the terminal has complete location data.
     */
    public function withLocation(): static
    {
        return $this->state(fn (array $attributes) => [
            'latitude' => $this->faker->latitude(24, 37),
            'longitude' => $this->faker->longitude(61, 78),
        ]);
    }

    /**
     * Indicate that the terminal has contact information.
     */
    public function withContact(): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => $this->faker->safeEmail(),
            'landmark' => $this->faker->randomElement([
                'Near Shopping Mall', 'Opposite Railway Station', 'Next to Hospital',
                'Behind City Hall', 'Near Airport', 'Close to University'
            ]),
        ]);
    }

    /**
     * Generate a unique terminal code based on city name
     */
    private function generateTerminalCode(string $cityName): string
    {
        // Get first 3 letters of city name
        $cityCode = strtoupper(substr($cityName, 0, 3));
        
        // Add a random number to make it unique
        $number = $this->faker->unique()->numberBetween(1, 999);
        
        return $cityCode . $number;
    }
}
