<?php

namespace App\Enums;

enum BookingTypeEnum: string
{
    case Online = 'online';
    case Counter = 'counter';
    case Phone = 'phone';

    public function label(): string
    {
        return match ($this) {
            self::Online => 'Online Booking',
            self::Counter => 'Counter Booking',
            self::Phone => 'Phone Booking',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Online => 'blue',
            self::Counter => 'green',
            self::Phone => 'yellow',
        };
    }
}
