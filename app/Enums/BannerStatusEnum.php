<?php

namespace App\Enums;

enum BannerStatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case DELETED = 'deleted';

    public static function getStatuses(): array
    {
        return [
            self::ACTIVE->value,
            self::INACTIVE->value,
            self::DELETED->value,
        ];
    }

    public static function getStatusName(string $status): string
    {
        return match ($status) {
            self::ACTIVE->value => 'Active',
            self::INACTIVE->value => 'Inactive',
            self::DELETED->value => 'Deleted',
        };
    }

    public static function getStatusColor(string $status): string
    {
        return match ($status) {
            self::ACTIVE->value => 'success',
            self::INACTIVE->value => 'warning',
            self::DELETED->value => 'danger',
        };
    }

    public static function getStatusValue(string $status): string
    {
        return match ($status) {
            self::ACTIVE->value => 'success',
            self::INACTIVE->value => 'warning',
            self::DELETED->value => 'danger',
            default => 'unknown',
        };
    }
}
