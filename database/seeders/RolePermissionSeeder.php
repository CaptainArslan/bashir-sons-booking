<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Super Admin',
            'Admin',
            'Customer',
            'Employee',
        ];
        // Create roles
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        $permissions = [
            'access admin panel',
            'manage users',
            'manage roles',
            'manage permissions',
            'view reports',
            'manage enquiries',
        ];

        // Create permissions
        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        // Assign all permissions to super admin
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $allPermissions = Permission::all();
        $superAdminRole->syncPermissions($allPermissions);

        // Assign limited permissions to admin
        $adminRole = Role::where('name', 'Admin')->first();
        $adminPermissions = Permission::whereIn('name', [
            'access admin panel',
            'manage users',
            'view reports',
            'manage enquiries',
        ])->get();
        $adminRole->syncPermissions($adminPermissions);

        // Customer and Employee have minimal or no permissions
        $customerRole = Role::where('name', 'Customer')->first();
        $employeeRole = Role::where('name', 'Employee')->first();

        $customerRole->syncPermissions([]);
        $employeeRole->syncPermissions([]);
    }
}
