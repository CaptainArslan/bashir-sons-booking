<?php

namespace Database\Seeders;

use App\Enums\AnnouncementAudienceTypeEnum;
use App\Enums\AnnouncementDisplayTypeEnum;
use App\Enums\AnnouncementPriorityEnum;
use App\Enums\AnnouncementStatusEnum;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user for created_by and updated_by
        $adminUser = User::where('email', 'admin@example.com')->first();

        if (! $adminUser) {
            $adminUser = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
            ]);
        }

        // Create sample announcements
        $announcements = [
            [
                'title' => 'Welcome to Bashir Sons Transport System',
                'description' => 'We are excited to announce the launch of our new transport management system. This system will help you manage routes, timetables, and bookings more efficiently.',
                'image' => null,
                'link' => null,
                'status' => AnnouncementStatusEnum::ACTIVE,
                'display_type' => AnnouncementDisplayTypeEnum::BANNER,
                'priority' => AnnouncementPriorityEnum::HIGH,
                'audience_type' => AnnouncementAudienceTypeEnum::ALL,
                'audience_payload' => null,
                'start_date' => now()->subDays(7),
                'end_date' => now()->addDays(30),
                'is_pinned' => true,
                'is_featured' => true,
                'is_active' => true,
                'created_by' => $adminUser->id,
                'updated_by' => $adminUser->id,
            ],
            [
                'title' => 'System Maintenance Scheduled',
                'description' => 'We will be performing scheduled maintenance on our system on Sunday, 2:00 AM - 4:00 AM. During this time, the system may be temporarily unavailable.',
                'image' => null,
                'link' => null,
                'status' => AnnouncementStatusEnum::ACTIVE,
                'display_type' => AnnouncementDisplayTypeEnum::POPUP,
                'priority' => AnnouncementPriorityEnum::HIGH,
                'audience_type' => AnnouncementAudienceTypeEnum::ALL,
                'audience_payload' => null,
                'start_date' => now()->subDays(2),
                'end_date' => now()->addDays(7),
                'is_pinned' => false,
                'is_featured' => false,
                'is_active' => true,
                'created_by' => $adminUser->id,
                'updated_by' => $adminUser->id,
            ],
            [
                'title' => 'New Route Added: Lahore to Karachi',
                'description' => 'We are pleased to announce a new direct route from Lahore to Karachi. Book your tickets now for the best prices!',
                'image' => null,
                'link' => '/routes',
                'status' => AnnouncementStatusEnum::ACTIVE,
                'display_type' => AnnouncementDisplayTypeEnum::BANNER,
                'priority' => AnnouncementPriorityEnum::MEDIUM,
                'audience_type' => AnnouncementAudienceTypeEnum::ALL,
                'audience_payload' => null,
                'start_date' => now()->subDays(1),
                'end_date' => now()->addDays(14),
                'is_pinned' => false,
                'is_featured' => true,
                'is_active' => true,
                'created_by' => $adminUser->id,
                'updated_by' => $adminUser->id,
            ],
            [
                'title' => 'Admin Training Session',
                'description' => 'All admin users are invited to attend a training session on the new features of the transport management system. Please register your attendance.',
                'image' => null,
                'link' => '/admin/training',
                'status' => AnnouncementStatusEnum::ACTIVE,
                'display_type' => AnnouncementDisplayTypeEnum::NOTIFICATION,
                'priority' => AnnouncementPriorityEnum::MEDIUM,
                'audience_type' => AnnouncementAudienceTypeEnum::ROLES,
                'audience_payload' => json_encode(['admin', 'manager']),
                'start_date' => now(),
                'end_date' => now()->addDays(10),
                'is_pinned' => false,
                'is_featured' => false,
                'is_active' => true,
                'created_by' => $adminUser->id,
                'updated_by' => $adminUser->id,
            ],
            [
                'title' => 'Holiday Schedule Update',
                'description' => 'Please note that our office will be closed on the following holidays. Regular services will resume the next working day.',
                'image' => null,
                'link' => null,
                'status' => AnnouncementStatusEnum::ACTIVE,
                'display_type' => AnnouncementDisplayTypeEnum::BANNER,
                'priority' => AnnouncementPriorityEnum::LOW,
                'audience_type' => AnnouncementAudienceTypeEnum::ALL,
                'audience_payload' => null,
                'start_date' => now()->addDays(5),
                'end_date' => now()->addDays(20),
                'is_pinned' => false,
                'is_featured' => false,
                'is_active' => true,
                'created_by' => $adminUser->id,
                'updated_by' => $adminUser->id,
            ],
            [
                'title' => 'Draft: New Payment Methods',
                'description' => 'We are working on adding new payment methods including digital wallets and cryptocurrency payments. This feature will be available soon.',
                'image' => null,
                'link' => null,
                'status' => AnnouncementStatusEnum::INACTIVE,
                'display_type' => AnnouncementDisplayTypeEnum::BANNER,
                'priority' => AnnouncementPriorityEnum::LOW,
                'audience_type' => AnnouncementAudienceTypeEnum::ALL,
                'audience_payload' => null,
                'start_date' => now()->addDays(30),
                'end_date' => now()->addDays(60),
                'is_pinned' => false,
                'is_featured' => false,
                'is_active' => false,
                'created_by' => $adminUser->id,
                'updated_by' => $adminUser->id,
            ],
        ];

        foreach ($announcements as $announcementData) {
            Announcement::create($announcementData);
        }

        // Create additional random announcements using factory
        Announcement::factory()
            ->count(10)
            ->active()
            ->create([
                'created_by' => $adminUser->id,
                'updated_by' => $adminUser->id,
            ]);

        // Create some pinned announcements
        Announcement::factory()
            ->count(3)
            ->pinned()
            ->active()
            ->create([
                'created_by' => $adminUser->id,
                'updated_by' => $adminUser->id,
            ]);

        // Create some featured announcements
        Announcement::factory()
            ->count(2)
            ->featured()
            ->active()
            ->create([
                'created_by' => $adminUser->id,
                'updated_by' => $adminUser->id,
            ]);

        // Create some popup announcements
        Announcement::factory()
            ->count(2)
            ->popup()
            ->active()
            ->create([
                'created_by' => $adminUser->id,
                'updated_by' => $adminUser->id,
            ]);

        // Create some role-specific announcements
        Announcement::factory()
            ->count(3)
            ->forRoles(['admin'])
            ->active()
            ->create([
                'created_by' => $adminUser->id,
                'updated_by' => $adminUser->id,
            ]);

        $this->command->info('Announcements seeded successfully!');
    }
}
