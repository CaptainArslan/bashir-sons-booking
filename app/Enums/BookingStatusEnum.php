<?php

namespace App\Enums;

enum BookingStatusEnum: string
{
    case HOLD = 'hold';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';

    public function getLabel(): string
    {
        return match ($this) {
            self::HOLD => 'Hold',
            self::CONFIRMED => 'Confirmed',
            self::CANCELLED => 'Cancelled',
            self::EXPIRED => 'Expired',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::HOLD => 'warning',
            self::CONFIRMED => 'success',
            self::CANCELLED => 'danger',
            self::EXPIRED => 'secondary',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::HOLD => 'fa-solid fa-clock',
            self::CONFIRMED => 'fa-solid fa-check-circle',
            self::CANCELLED => 'fa-solid fa-times-circle',
            self::EXPIRED => 'fa-solid fa-clock',
        };
    }

    public function getBadge(): string
    {
        return match ($this) {
            self::HOLD => 'badge bg-warning',
            self::CONFIRMED => 'badge bg-success',
            self::CANCELLED => 'badge bg-danger',
            self::EXPIRED => 'badge bg-secondary',
        };
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getName(): string
    {
        return self::getLabel($this->value);
    }
}
