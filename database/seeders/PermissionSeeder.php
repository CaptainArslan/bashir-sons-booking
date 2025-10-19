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
            'access admin panel',
            'view dashboard',

            // User Management
            'view users',
            'create users',
            'edit users',
            'delete users',
            'manage user profiles',

            // Role Management
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'assign roles',

            // Permission Management
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',
            'assign permissions',

            // City Management
            'view cities',
            'create cities',
            'edit cities',
            'delete cities',

            // Terminal Management
            'view terminals',
            'create terminals',
            'edit terminals',
            'delete terminals',

            // Bus Type Management
            'view bus types',
            'create bus types',
            'edit bus types',
            'delete bus types',

            // Bus Layout Management
            'view bus layouts',
            'create bus layouts',
            'edit bus layouts',
            'delete bus layouts',

            // Facility Management
            'view facilities',
            'create facilities',
            'edit facilities',
            'delete facilities',

            // Bus Management
            'view buses',
            'create buses',
            'edit buses',
            'delete buses',
            'manage bus facilities',

            // Banner Management
            'view banners',
            'create banners',
            'edit banners',
            'delete banners',

            // General Settings
            'view general settings',
            'create general settings',
            'edit general settings',
            'delete general settings',

            // Route Management
            'view routes',
            'create routes',
            'edit routes',
            'delete routes',

            // Route Stop Management
            'view route stops',
            'create route stops',
            'edit route stops',
            'delete route stops',

            // Route Fare Management
            'view route fares',
            'create route fares',
            'edit route fares',
            'delete route fares',

            // Route Timetable Management
            'view route timetables',
            'create route timetables',
            'edit route timetables',
            'delete route timetables',

            // Route Stop Time Management
            'view route stop times',
            'create route stop times',
            'edit route stop times',
            'delete route stop times',

            // Schedule Management
            'view schedules',
            'create schedules',
            'edit schedules',
            'delete schedules',

            // Enquiry Management
            'view enquiries',
            'delete enquiries',
            'reply to enquiries',

            // Reports
            'view reports',
            'export reports',

            // System
            'manage system settings',
            'view system logs',
            'backup system',
        ];

        // Create permissions
        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }
    }
}
