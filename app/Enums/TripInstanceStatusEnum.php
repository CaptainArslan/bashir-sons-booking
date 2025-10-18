<?php

namespace App\Enums;

enum TripInstanceStatusEnum: string
{
    case PENDING = 'pending';
    case BOARDING = 'boarding';
    case DEPARTED = 'departed';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public static function getStatuses(): array
    {
        return [
            self::PENDING->value,
            self::BOARDING->value,
            self::DEPARTED->value,
            self::COMPLETED->value,
            self::CANCELLED->value,
        ];
    }

    public static function getStatusName(string $status): string
    {
        return match ($status) {
            self::PENDING->value => 'Pending',
            self::BOARDING->value => 'Boarding',
            self::DEPARTED->value => 'Departed',
            self::COMPLETED->value => 'Completed',
            self::CANCELLED->value => 'Cancelled',
        };
    }

    public static function getStatusColor(string $status): string
    {
        return match ($status) {
            self::PENDING->value => 'warning',
            self::BOARDING->value => 'info',
            self::DEPARTED->value => 'success',
            self::COMPLETED->value => 'secondary',
            self::CANCELLED->value => 'danger',
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
