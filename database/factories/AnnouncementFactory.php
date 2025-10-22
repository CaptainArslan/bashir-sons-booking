<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Announcement;
use App\Enums\AnnouncementStatusEnum;
use App\Enums\AnnouncementPriorityEnum;
use App\Enums\AnnouncementDisplayTypeEnum;
use App\Enums\AnnouncementAudienceTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Announcement>
 */
class AnnouncementFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Announcement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-30 days', '+30 days');
        $endDate = $this->faker->dateTimeBetween($startDate, '+60 days');

        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(3),
            'image' => $this->faker->optional(0.7)->imageUrl(800, 400, 'business', true),
            'link' => $this->faker->optional(0.5)->url(),
            'status' => $this->faker->randomElement(AnnouncementStatusEnum::cases()),
            'display_type' => $this->faker->randomElement(AnnouncementDisplayTypeEnum::cases()),
            'priority' => $this->faker->randomElement(AnnouncementPriorityEnum::cases()),
            'audience_type' => $this->faker->randomElement(AnnouncementAudienceTypeEnum::cases()),
            'audience_payload' => $this->faker->optional(0.3)->passthrough(json_encode($this->faker->randomElements(['admin', 'user', 'manager'], 2))),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_pinned' => $this->faker->boolean(20),
            'is_featured' => $this->faker->boolean(15),
            'is_active' => $this->faker->boolean(85),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the announcement is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'status' => AnnouncementStatusEnum::ACTIVE,
            'start_date' => now()->subDays(7),
            'end_date' => now()->addDays(30),
        ]);
    }

    /**
     * Indicate that the announcement is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'status' => AnnouncementStatusEnum::INACTIVE,
        ]);
    }

    /**
     * Indicate that the announcement is pinned.
     */
    public function pinned(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_pinned' => true,
            'priority' => AnnouncementPriorityEnum::HIGH,
        ]);
    }

    /**
     * Indicate that the announcement is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'priority' => AnnouncementPriorityEnum::HIGH,
        ]);
    }

    /**
     * Indicate that the announcement is a banner.
     */
    public function banner(): static
    {
        return $this->state(fn (array $attributes) => [
            'display_type' => AnnouncementDisplayTypeEnum::BANNER,
            'priority' => AnnouncementPriorityEnum::MEDIUM,
        ]);
    }

    /**
     * Indicate that the announcement is a popup.
     */
    public function popup(): static
    {
        return $this->state(fn (array $attributes) => [
            'display_type' => AnnouncementDisplayTypeEnum::POPUP,
            'priority' => AnnouncementPriorityEnum::HIGH,
        ]);
    }

    /**
     * Indicate that the announcement is for all users.
     */
    public function forAll(): static
    {
        return $this->state(fn (array $attributes) => [
            'audience_type' => AnnouncementAudienceTypeEnum::ALL,
            'audience_payload' => null,
        ]);
    }

    /**
     * Indicate that the announcement is for specific roles.
     */
    public function forRoles(array $roles = ['admin']): static
    {
        return $this->state(fn (array $attributes) => [
            'audience_type' => AnnouncementAudienceTypeEnum::ROLES,
            'audience_payload' => json_encode($roles),
        ]);
    }
}