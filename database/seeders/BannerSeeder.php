<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Enums\BannerTypeEnum;
use App\Enums\BannerStatusEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banners = [
            // Main Banners
            [
                'title' => 'Welcome to Bashir Sons Bus Service',
                'type' => BannerTypeEnum::MAIN->value,
                'path' => 'banners/main/welcome-banner.jpg',
                'status' => BannerStatusEnum::ACTIVE->value,
                'order' => 1,
            ],
            [
                'title' => 'Comfortable & Safe Travel Experience',
                'type' => BannerTypeEnum::MAIN->value,
                'path' => 'banners/main/comfort-banner.jpg',
                'status' => BannerStatusEnum::ACTIVE->value,
                'order' => 2,
            ],
            [
                'title' => 'Book Your Journey Today',
                'type' => BannerTypeEnum::MAIN->value,
                'path' => 'banners/main/booking-banner.jpg',
                'status' => BannerStatusEnum::ACTIVE->value,
                'order' => 3,
            ],

            // Slider Banners
            [
                'title' => 'Luxury Bus Services Available',
                'type' => BannerTypeEnum::SLIDER->value,
                'path' => 'banners/slider/luxury-bus.jpg',
                'status' => BannerStatusEnum::ACTIVE->value,
                'order' => 1,
            ],
            [
                'title' => 'Air Conditioned Fleet',
                'type' => BannerTypeEnum::SLIDER->value,
                'path' => 'banners/slider/ac-bus.jpg',
                'status' => BannerStatusEnum::ACTIVE->value,
                'order' => 2,
            ],
            [
                'title' => '24/7 Customer Support',
                'type' => BannerTypeEnum::SLIDER->value,
                'path' => 'banners/slider/support.jpg',
                'status' => BannerStatusEnum::ACTIVE->value,
                'order' => 3,
            ],
            [
                'title' => 'Online Booking Made Easy',
                'type' => BannerTypeEnum::SLIDER->value,
                'path' => 'banners/slider/online-booking.jpg',
                'status' => BannerStatusEnum::ACTIVE->value,
                'order' => 4,
            ],

            // Inner Page Banners
            [
                'title' => 'About Our Services',
                'type' => BannerTypeEnum::INNER->value,
                'path' => 'banners/inner/about-us.jpg',
                'status' => BannerStatusEnum::ACTIVE->value,
                'order' => 1,
            ],
            [
                'title' => 'Our Bus Fleet',
                'type' => BannerTypeEnum::INNER->value,
                'path' => 'banners/inner/fleet.jpg',
                'status' => BannerStatusEnum::ACTIVE->value,
                'order' => 2,
            ],
            [
                'title' => 'Contact Information',
                'type' => BannerTypeEnum::INNER->value,
                'path' => 'banners/inner/contact.jpg',
                'status' => BannerStatusEnum::ACTIVE->value,
                'order' => 3,
            ],
            [
                'title' => 'Terms & Conditions',
                'type' => BannerTypeEnum::INNER->value,
                'path' => 'banners/inner/terms.jpg',
                'status' => BannerStatusEnum::ACTIVE->value,
                'order' => 4,
            ],

            // Promotion Banners
            [
                'title' => 'Special Discount - Book Now!',
                'type' => BannerTypeEnum::PROMOTION->value,
                'path' => 'banners/promotion/discount.jpg',
                'status' => BannerStatusEnum::ACTIVE->value,
                'order' => 1,
            ],
            [
                'title' => 'Early Bird Offer - 20% Off',
                'type' => BannerTypeEnum::PROMOTION->value,
                'path' => 'banners/promotion/early-bird.jpg',
                'status' => BannerStatusEnum::ACTIVE->value,
                'order' => 2,
            ],
            [
                'title' => 'Group Booking Discounts Available',
                'type' => BannerTypeEnum::PROMOTION->value,
                'path' => 'banners/promotion/group-booking.jpg',
                'status' => BannerStatusEnum::ACTIVE->value,
                'order' => 3,
            ],
            [
                'title' => 'Student Discount - 15% Off',
                'type' => BannerTypeEnum::PROMOTION->value,
                'path' => 'banners/promotion/student-discount.jpg',
                'status' => BannerStatusEnum::ACTIVE->value,
                'order' => 4,
            ],

            // Other Banners
            [
                'title' => 'Safety First - Our Priority',
                'type' => BannerTypeEnum::OTHER->value,
                'path' => 'banners/other/safety.jpg',
                'status' => BannerStatusEnum::ACTIVE->value,
                'order' => 1,
            ],
            [
                'title' => 'Download Our Mobile App',
                'type' => BannerTypeEnum::OTHER->value,
                'path' => 'banners/other/mobile-app.jpg',
                'status' => BannerStatusEnum::ACTIVE->value,
                'order' => 2,
            ],
            [
                'title' => 'Follow Us on Social Media',
                'type' => BannerTypeEnum::OTHER->value,
                'path' => 'banners/other/social-media.jpg',
                'status' => BannerStatusEnum::ACTIVE->value,
                'order' => 3,
            ],

            // Inactive Banners (for testing)
            [
                'title' => 'Coming Soon - New Routes',
                'type' => BannerTypeEnum::PROMOTION->value,
                'path' => 'banners/promotion/coming-soon.jpg',
                'status' => BannerStatusEnum::INACTIVE->value,
                'order' => 5,
            ],
            [
                'title' => 'Maintenance Notice',
                'type' => BannerTypeEnum::OTHER->value,
                'path' => 'banners/other/maintenance.jpg',
                'status' => BannerStatusEnum::INACTIVE->value,
                'order' => 4,
            ],
        ];

        foreach ($banners as $banner) {
            Banner::firstOrCreate($banner);
        }
    }
}
