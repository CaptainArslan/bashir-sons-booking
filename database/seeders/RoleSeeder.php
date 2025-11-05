<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $roles = [
            'Super Admin',
            'Admin',
            'Manager',
            'Employee',
            'Customer',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Super Admin - All permissions (including access admin panel)
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $allPermissions = Permission::all();
        $superAdminRole->syncPermissions($allPermissions);

        // Admin - Most permissions except system management
        // Admin role automatically gets 'access admin panel' since it's included in all permissions
        $adminRole = Role::where('name', 'Admin')->first();
        $adminPermissions = Permission::whereNotIn('name', [
            'manage system settings',
            'view system logs',
            'backup system',
            'delete users',
            'delete roles',
            'delete permissions',
        ])->get();
        $adminRole->syncPermissions($adminPermissions);

        // Manager - Business operations management
        // Manager role explicitly gets 'access admin panel' permission to access admin dashboard
        $managerRole = Role::where('name', 'Manager')->first();
        $managerPermissions = Permission::whereIn('name', [
            'access admin panel', // Required to access admin dashboard
            'view dashboard',
            'view users',
            'view cities',
            'view terminals',
            'view bus types',
            'view bus layouts',
            'view facilities',
            'view buses',
            'view banners',
            'view announcements',
            'view general settings',
            'view enquiries',
            'view reports',
            'create cities',
            'edit cities',
            'create terminals',
            'edit terminals',
            'create bus types',
            'edit bus types',
            'create bus layouts',
            'edit bus layouts',
            'create facilities',
            'edit facilities',
            'create buses',
            'edit buses',
            'create banners',
            'edit banners',
            'create announcements',
            'edit announcements',
            'view discounts',
            'create discounts',
            'edit discounts',
            'edit general settings',
            'view routes',
            'create routes',
            'edit routes',
            'view route stops',
            'create route stops',
            'edit route stops',
            'view route fares',
            'create route fares',
            'edit route fares',
            'delete enquiries',
            'reply to enquiries',
            'export reports',
        ])->get();
        $managerRole->syncPermissions($managerPermissions);

        // Employee - Limited operational permissions
        // Employee role explicitly gets 'access admin panel' permission to access admin dashboard
        $employeeRole = Role::where('name', 'Employee')->first();
        $employeePermissions = Permission::whereIn('name', [
            'access admin panel', // Required to access admin dashboard
            'view dashboard',
            'view cities',
            'view terminals',
            'view bus types',
            'view bus layouts',
            'view facilities',
            'view buses',
            'view routes',
            'view route stops',
            'view route fares',
            'view banners',
            'view announcements',
            'view discounts',
            'view enquiries',
            'view reports',
            'edit buses',
            'delete enquiries',
            'reply to enquiries',
        ])->get();
        $employeeRole->syncPermissions($employeePermissions);

        // Customer - No admin permissions
        $customerRole = Role::where('name', 'Customer')->first();
        $customerRole->syncPermissions([]);
    }
}
