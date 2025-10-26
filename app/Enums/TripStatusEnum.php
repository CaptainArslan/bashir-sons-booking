<?php

namespace App\Enums;

enum TripStatusEnum: string
{
    case Pending = 'pending';
    case Scheduled = 'scheduled';
    case Boarding = 'boarding';
    case Ongoing = 'ongoing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Delayed = 'delayed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Scheduled => 'Scheduled',
            self::Boarding => 'Boarding',
            self::Ongoing => 'On Going',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
            self::Delayed => 'Delayed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Scheduled => 'blue',
            self::Boarding => 'indigo',
            self::Ongoing => 'green',
            self::Completed => 'teal',
            self::Cancelled => 'red',
            self::Delayed => 'orange',
        };
    }

    public function allowsBooking(): bool
    {
        return in_array($this, [self::Pending, self::Scheduled, self::Boarding]);
    }

    public function allowsBusAssignment(): bool
    {
        return in_array($this, [self::Pending, self::Scheduled]);
    }

    public function allowsExpenses(): bool
    {
        return $this !== self::Pending && $this !== self::Cancelled;
    }
}
