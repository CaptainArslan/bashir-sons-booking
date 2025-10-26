<?php

namespace App\Helpers;

class PermissionHelper
{
    /**
     * Generate action buttons based on permissions
     */
    public static function generateActionButtons(string $resource, int $id, array $permissions = []): string
    {
        $buttons = '<div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                    type="button" 
                    data-bs-toggle="dropdown" 
                    aria-expanded="false">
                <i class="bx bx-dots-horizontal-rounded"></i>
            </button>
            <ul class="dropdown-menu">';

        // Edit button
        if (isset($permissions['edit']) && $permissions['edit']) {
            $buttons .= '<li>
                <a class="dropdown-item" href="'.route("admin.{$resource}.edit", $id).'">
                    <i class="bx bx-edit me-2"></i>Edit '.ucfirst(str_replace('-', ' ', $resource))
                .'</a>
            </li>';
        }

        // Delete button
        if (isset($permissions['delete']) && $permissions['delete']) {
            $buttons .= '<li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item text-danger" 
                   href="javascript:void(0)" 
                   onclick="delete'.ucfirst(str_replace('-', '', $resource)).'('.$id.')">
                    <i class="bx bx-trash me-2"></i>Delete '.ucfirst(str_replace('-', ' ', $resource))
                .'</a>
            </li>';
        }

        $buttons .= '</ul></div>';

        return $buttons;
    }

    /**
     * Check if user has permission
     */
    public static function hasPermission(string $permission): bool
    {
        return auth()->user()->can($permission);
    }

    /**
     * Get resource permissions
     */
    public static function getResourcePermissions(string $resource): array
    {
        return [
            'view' => self::hasPermission("view {$resource}"),
            'create' => self::hasPermission("create {$resource}"),
            'edit' => self::hasPermission("edit {$resource}"),
            'delete' => self::hasPermission("delete {$resource}"),
        ];
    }
}
