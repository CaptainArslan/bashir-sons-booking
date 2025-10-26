<?php

namespace App\Enums;

enum BookingStatusEnum: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
    case Completed = 'completed';
    case NoShow = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Cancelled => 'Cancelled',
            self::Completed => 'Completed',
            self::NoShow => 'No Show',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Confirmed => 'green',
            self::Cancelled => 'red',
            self::Completed => 'blue',
            self::NoShow => 'gray',
        };
    }

    public function canBeCancelled(): bool
    {
        return $this === self::Pending || $this === self::Confirmed;
    }
}
