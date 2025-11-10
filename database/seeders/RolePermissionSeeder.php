<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $roles = [
            'super_admin',
            'admin',
            'employee',
            'customer',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(
                ['name' => $roleName],
                ['guard_name' => 'web']
            );
        }

        // Ensure PermissionSeeder has run first
        $this->command->info('Assuming PermissionSeeder has already run. If not, run it first.');

        // Super Admin - All permissions (including access admin panel)
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $allPermissions = Permission::all();
            $superAdminRole->syncPermissions($allPermissions);
            $this->command->info('Super Admin role assigned all '.$allPermissions->count().' permissions (including access admin panel).');
        }

        // Admin - Most permissions except system management and super admin restrictions
        // Admin role automatically gets 'access admin panel' since it's included in all permissions
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminPermissions = Permission::whereNotIn('name', [
                'manage system settings',
                'view system logs',
                'backup system',
                'delete roles',
                'delete permissions',
            ])->get();
            $adminRole->syncPermissions($adminPermissions);
            $this->command->info('Admin role assigned '.$adminPermissions->count().' permissions (including access admin panel).');
        }

        // Employee - Full booking management and viewing permissions
        // Employee role explicitly gets 'access admin panel' permission to access admin dashboard
        $employeeRole = Role::where('name', 'employee')->first();
        if ($employeeRole) {
            $employeePermissions = Permission::whereIn('name', [
                'access admin panel', // Required to access admin dashboard
                'view dashboard',
                'view cities',
                'view terminals',
                'view routes',
                'view route stops',
                'view fares',
                'view timetables',
                'view buses',
                'view bus types',
                'view bus layouts',
                'view facilities',
                // Full booking management
                'view bookings',
                'create bookings',
                'edit bookings',
                'delete bookings',
                // Enquiry management
                'view enquiries',
                'reply to enquiries',
                'delete enquiries',
                // Content viewing
                'view banners',
                'view announcements',
                'view discounts',
                // Terminal reports only
                'view terminal reports',
            ])->get();
            $employeeRole->syncPermissions($employeePermissions);
            $this->command->info('Employee role assigned '.$employeePermissions->count().' permissions.');
        }

        // Customer - No admin permissions (frontend only)
        $customerRole = Role::where('name', 'customer')->first();
        if ($customerRole) {
            $customerRole->syncPermissions([]);
            $this->command->info('Customer role has no admin permissions.');
        }
    }
}
