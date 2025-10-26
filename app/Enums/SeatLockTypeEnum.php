<?php

namespace App\Enums;

enum SeatLockTypeEnum: string
{
    case Temporary = 'temporary';
    case PhoneHold = 'phone_hold';
    case Reserved = 'reserved';

    public function label(): string
    {
        return match ($this) {
            self::Temporary => 'Temporary Lock',
            self::PhoneHold => 'Phone Hold',
            self::Reserved => 'Reserved',
        };
    }

    public function defaultTTL(): int
    {
        return match ($this) {
            self::Temporary => 300, // 5 minutes
            self::PhoneHold => 1800, // 30 minutes before departure
            self::Reserved => 0, // No expiry
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Temporary => 'yellow',
            self::PhoneHold => 'orange',
            self::Reserved => 'red',
        };
    }
}
