<?php

namespace App\Enums;

enum BusTypeEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    public static function getStatuses(): array
    {
        return [
            self::ACTIVE->value,
            self::INACTIVE->value,
        ];
    }

    public static function getStatusName(string $status): string
    {
        return match ($status) {
            self::ACTIVE->value => 'Active',
            self::INACTIVE->value => 'Inactive',
        };
    }

    public static function getStatusColor(string $status): string
    {
        return match ($status) {
            self::ACTIVE->value => 'success',
            self::INACTIVE->value => 'warning',
        };
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getName(): string
    {
        return self::getStatusName($this->value);
    }
}
