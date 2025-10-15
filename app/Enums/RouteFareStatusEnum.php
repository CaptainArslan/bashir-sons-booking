<?php

namespace App\Enums;

enum RouteFareStatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';

    /**
     * Get all status values
     */
    public static function getStatuses(): array
    {
        return [
            self::ACTIVE->value,
            self::INACTIVE->value,
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
            self::ACTIVE->value => 'Fare is active and available for booking',
            self::INACTIVE->value => 'Fare is temporarily unavailable',
            self::SUSPENDED->value => 'Fare is suspended due to operational issues',
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

    /**
     * Get current enum value
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Get current enum name
     */
    public function getName(): string
    {
        return self::getStatusName($this->value);
    }
}
