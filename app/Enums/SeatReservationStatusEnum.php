<?php

namespace App\Enums;

enum SeatReservationStatusEnum: string
{
    case PENDING = 'pending';
    case RESERVED = 'reserved';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';

    public static function getStatuses(): array
    {
        return [
            self::PENDING->value,
            self::RESERVED->value,
            self::CONFIRMED->value,
            self::CANCELLED->value,
        ];
    }

    public static function getStatusName(string $status): string
    {
        return match ($status) {
            self::PENDING->value => 'Pending',
            self::RESERVED->value => 'Reserved',
            self::CONFIRMED->value => 'Confirmed',
            self::CANCELLED->value => 'Cancelled',
        };
    }

    public static function getStatusColor(string $status): string
    {
        return match ($status) {
            self::PENDING->value => 'warning',
            self::RESERVED->value => 'info',
            self::CONFIRMED->value => 'secondary',
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

    public function getColor(): string
    {
        return self::getStatusColor($this->value);
    }
}
