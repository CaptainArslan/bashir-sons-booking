<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Dashboard
            ['name' => 'access admin panel', 'module' => 'Dashboard'],
            ['name' => 'view dashboard', 'module' => 'Dashboard'],

            // User Management
            ['name' => 'view users', 'module' => 'User Management'],
            ['name' => 'create users', 'module' => 'User Management'],
            ['name' => 'edit users', 'module' => 'User Management'],
            ['name' => 'delete users', 'module' => 'User Management'],
            ['name' => 'ban users', 'module' => 'User Management'],
            ['name' => 'activate users', 'module' => 'User Management'],
            ['name' => 'manage user profiles', 'module' => 'User Management'],
            ['name' => 'manage users', 'module' => 'User Management'],

            // Role Management
            ['name' => 'view roles', 'module' => 'Role Management'],
            ['name' => 'create roles', 'module' => 'Role Management'],
            ['name' => 'edit roles', 'module' => 'Role Management'],
            ['name' => 'delete roles', 'module' => 'Role Management'],
            ['name' => 'assign roles', 'module' => 'Role Management'],

            // Permission Management
            ['name' => 'view permissions', 'module' => 'Permission Management'],
            ['name' => 'create permissions', 'module' => 'Permission Management'],
            ['name' => 'edit permissions', 'module' => 'Permission Management'],
            ['name' => 'delete permissions', 'module' => 'Permission Management'],
            ['name' => 'assign permissions', 'module' => 'Permission Management'],

            // City Management
            ['name' => 'view cities', 'module' => 'City Management'],
            ['name' => 'create cities', 'module' => 'City Management'],
            ['name' => 'edit cities', 'module' => 'City Management'],
            ['name' => 'delete cities', 'module' => 'City Management'],

            // Terminal Management
            ['name' => 'view terminals', 'module' => 'Terminal Management'],
            ['name' => 'create terminals', 'module' => 'Terminal Management'],
            ['name' => 'edit terminals', 'module' => 'Terminal Management'],
            ['name' => 'delete terminals', 'module' => 'Terminal Management'],

            // Bus Type Management
            ['name' => 'view bus types', 'module' => 'Bus Type Management'],
            ['name' => 'create bus types', 'module' => 'Bus Type Management'],
            ['name' => 'edit bus types', 'module' => 'Bus Type Management'],
            ['name' => 'delete bus types', 'module' => 'Bus Type Management'],

            // Facility Management
            ['name' => 'view facilities', 'module' => 'Facility Management'],
            ['name' => 'create facilities', 'module' => 'Facility Management'],
            ['name' => 'edit facilities', 'module' => 'Facility Management'],
            ['name' => 'delete facilities', 'module' => 'Facility Management'],

            // Bus Management
            ['name' => 'view buses', 'module' => 'Bus Management'],
            ['name' => 'create buses', 'module' => 'Bus Management'],
            ['name' => 'edit buses', 'module' => 'Bus Management'],
            ['name' => 'delete buses', 'module' => 'Bus Management'],
            ['name' => 'manage bus facilities', 'module' => 'Bus Management'],

            // Banner Management
            ['name' => 'view banners', 'module' => 'Banner Management'],
            ['name' => 'create banners', 'module' => 'Banner Management'],
            ['name' => 'edit banners', 'module' => 'Banner Management'],
            ['name' => 'delete banners', 'module' => 'Banner Management'],

            // Announcement Management
            ['name' => 'view announcements', 'module' => 'Announcement Management'],
            ['name' => 'create announcements', 'module' => 'Announcement Management'],
            ['name' => 'edit announcements', 'module' => 'Announcement Management'],
            ['name' => 'delete announcements', 'module' => 'Announcement Management'],

            // Discount Management
            ['name' => 'view discounts', 'module' => 'Discount Management'],
            ['name' => 'create discounts', 'module' => 'Discount Management'],
            ['name' => 'edit discounts', 'module' => 'Discount Management'],
            ['name' => 'delete discounts', 'module' => 'Discount Management'],

            // General Settings
            ['name' => 'view general settings', 'module' => 'General Settings'],
            ['name' => 'create general settings', 'module' => 'General Settings'],
            ['name' => 'edit general settings', 'module' => 'General Settings'],
            ['name' => 'delete general settings', 'module' => 'General Settings'],

            // Route Management
            ['name' => 'view routes', 'module' => 'Route Management'],
            ['name' => 'create routes', 'module' => 'Route Management'],
            ['name' => 'edit routes', 'module' => 'Route Management'],
            ['name' => 'delete routes', 'module' => 'Route Management'],

            // Route Stop Management
            ['name' => 'view route stops', 'module' => 'Route Stop Management'],
            ['name' => 'create route stops', 'module' => 'Route Stop Management'],
            ['name' => 'edit route stops', 'module' => 'Route Stop Management'],
            ['name' => 'delete route stops', 'module' => 'Route Stop Management'],

            // Fare Management
            ['name' => 'view fares', 'module' => 'Fare Management'],
            ['name' => 'create fares', 'module' => 'Fare Management'],
            ['name' => 'edit fares', 'module' => 'Fare Management'],
            ['name' => 'delete fares', 'module' => 'Fare Management'],

            // Timetable Management
            ['name' => 'view timetables', 'module' => 'Timetable Management'],
            ['name' => 'create timetables', 'module' => 'Timetable Management'],
            ['name' => 'edit timetables', 'module' => 'Timetable Management'],
            ['name' => 'delete timetables', 'module' => 'Timetable Management'],

            // Booking Management
            ['name' => 'view bookings', 'module' => 'Booking Management'],
            ['name' => 'create bookings', 'module' => 'Booking Management'],
            ['name' => 'edit bookings', 'module' => 'Booking Management'],
            ['name' => 'delete bookings', 'module' => 'Booking Management'],

            // Enquiry Management
            ['name' => 'view enquiries', 'module' => 'Enquiry Management'],
            ['name' => 'delete enquiries', 'module' => 'Enquiry Management'],
            ['name' => 'reply to enquiries', 'module' => 'Enquiry Management'],

            // Reports
            ['name' => 'view reports', 'module' => 'Reports'],
            ['name' => 'view terminal reports', 'module' => 'Reports'],
            ['name' => 'view all booking reports', 'module' => 'Reports'],
            ['name' => 'export reports', 'module' => 'Reports'],

            // System
            ['name' => 'manage system settings', 'module' => 'System'],
            ['name' => 'view system logs', 'module' => 'System'],
            ['name' => 'backup system', 'module' => 'System'],
        ];

        // Create permissions with module
        foreach ($permissions as $permissionData) {
            Permission::updateOrCreate(
                ['name' => $permissionData['name']],
                ['module' => $permissionData['module'], 'guard_name' => 'web']
            );
        }
    }
}
