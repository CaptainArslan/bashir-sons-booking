<?php

namespace App\Traits;

trait HasPermissions
{
    /**
     * Check if user has permission to perform action
     */
    protected function hasPermission(string $permission): bool
    {
        return auth()->user()->can($permission);
    }

    /**
     * Check if user has any of the given permissions
     */
    protected function hasAnyPermission(array $permissions): bool
    {
        return auth()->user()->hasAnyPermission($permissions);
    }

    /**
     * Check if user has all of the given permissions
     */
    protected function hasAllPermissions(array $permissions): bool
    {
        return auth()->user()->hasAllPermissions($permissions);
    }

    /**
     * Check if user has role
     */
    protected function hasRole(string $role): bool
    {
        return auth()->user()->hasRole($role);
    }

    /**
     * Check if user has any of the given roles
     */
    protected function hasAnyRole(array $roles): bool
    {
        return auth()->user()->hasAnyRole($roles);
    }

    /**
     * Get user's permissions for a specific resource
     */
    protected function getResourcePermissions(string $resource): array
    {
        $permissions = [
            'view' => $this->hasPermission("view {$resource}"),
            'create' => $this->hasPermission("create {$resource}"),
            'edit' => $this->hasPermission("edit {$resource}"),
            'delete' => $this->hasPermission("delete {$resource}"),
        ];

        return $permissions;
    }

    /**
     * Share permissions with view
     */
    protected function sharePermissionsWithView(string $resource): void
    {
        view()->share('permissions', $this->getResourcePermissions($resource));
    }
}
