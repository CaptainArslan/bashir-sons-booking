<?php

namespace App\Enums;

enum BookingStatusEnum: string
{
    case PENDING = 'pending';
    case HELD = 'held';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';

    public static function getStatuses(): array
    {
        return [
            self::PENDING->value,
            self::HELD->value,
            self::CONFIRMED->value,
            self::CANCELLED->value,
            self::EXPIRED->value,
        ];
    }
    public static function getStatusName(string $status): string
    {
        return match ($status) {
            self::PENDING->value => 'Pending',
            self::HELD->value => 'Held',
            self::CONFIRMED->value => 'Confirmed',
            self::CANCELLED->value => 'Cancelled',
            self::EXPIRED->value => 'Expired',
        };
    }
    public static function getStatusColor(string $status): string
    {
        return match ($status) {
            self::PENDING->value => 'warning',
            self::HELD->value => 'info',
            self::CONFIRMED->value => 'success',
            self::CANCELLED->value => 'danger',
            self::EXPIRED->value => 'secondary',
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
    public function getColor(): string
    {
        return self::getStatusColor($this->value);
    }
}
