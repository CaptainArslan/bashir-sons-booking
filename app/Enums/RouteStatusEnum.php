<?php

namespace App\Enums;

enum RouteStatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case MAINTENANCE = 'maintenance';
    case SUSPENDED = 'suspended';

    /**
     * Get all status values
     */
    public static function getStatuses(): array
    {
        return [
            self::ACTIVE->value,
            self::INACTIVE->value,
            self::MAINTENANCE->value,
            self::SUSPENDED->value,
        ];
    }

    /**
     * Get status name for display
     */
    public static function getStatusName(string $status): string
    {
        return match ($status) {
            self::ACTIVE->value => 'Active',
            self::INACTIVE->value => 'Inactive',
            self::MAINTENANCE->value => 'Under Maintenance',
            self::SUSPENDED->value => 'Suspended',
            default => 'Unknown',
        };
    }

    /**
     * Get status color for badges
     */
    public static function getStatusColor(string $status): string
    {
        return match ($status) {
            self::ACTIVE->value => 'success',
            self::INACTIVE->value => 'secondary',
            self::MAINTENANCE->value => 'warning',
            self::SUSPENDED->value => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get status icon
     */
    public static function getStatusIcon(string $status): string
    {
        return match ($status) {
            self::ACTIVE->value => 'bx-check-circle',
            self::INACTIVE->value => 'bx-x-circle',
            self::MAINTENANCE->value => 'bx-wrench',
            self::SUSPENDED->value => 'bx-stop-circle',
            default => 'bx-help-circle',
        };
    }

    /**
     * Get status description
     */
    public static function getStatusDescription(string $status): string
    {
        return match ($status) {
            self::ACTIVE->value => 'Route is operational and accepting bookings',
            self::INACTIVE->value => 'Route is temporarily unavailable',
            self::MAINTENANCE->value => 'Route is under maintenance or repair',
            self::SUSPENDED->value => 'Route is suspended due to operational issues',
            default => 'Unknown status',
        };
    }

    /**
     * Check if status allows bookings
     */
    public static function allowsBookings(string $status): bool
    {
        return $status === self::ACTIVE->value;
    }

    /**
     * Get available status transitions
     */
    public static function getAvailableTransitions(string $currentStatus): array
    {
        return match ($currentStatus) {
            self::ACTIVE->value => [
                self::INACTIVE->value,
                self::MAINTENANCE->value,
                self::SUSPENDED->value,
            ],
            self::INACTIVE->value => [
                self::ACTIVE->value,
                self::MAINTENANCE->value,
            ],
            self::MAINTENANCE->value => [
                self::ACTIVE->value,
                self::INACTIVE->value,
            ],
            self::SUSPENDED->value => [
                self::ACTIVE->value,
                self::INACTIVE->value,
            ],
            default => [],
        };
    }

    /**
     * Get status badge HTML
     */
    public static function getStatusBadge(string $status): string
    {
        $name = self::getStatusName($status);
        $color = self::getStatusColor($status);
        $icon = self::getStatusIcon($status);
        
        return '<span class="badge bg-' . $color . '">
                    <i class="bx ' . $icon . ' me-1"></i>' . e($name) . '
                </span>';
    }

    /**
     * Get status options for select dropdown
     */
    public static function getStatusOptions(): array
    {
        $options = [];
        foreach (self::getStatuses() as $status) {
            $options[$status] = self::getStatusName($status);
        }
        return $options;
    }
}
