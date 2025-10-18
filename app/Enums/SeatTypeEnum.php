<?php

namespace App\Enums;

enum SeatTypeEnum: string
{
    case WINDOW = 'window';
    case AISLE = 'aisle';
    case MIDDLE = 'middle';
    case EXECUTIVE = 'executive';
    case SLEEPER = 'sleeper';
    case SEMI_SLEEPER = 'semi_sleeper';
    case DISABLED = 'disabled';
    case VIP = 'vip';

    public static function getSeatTypes(): array
    {
        return [
            self::WINDOW->value,
            self::AISLE->value,
            self::MIDDLE->value,
            self::EXECUTIVE->value,
            self::SLEEPER->value,
            self::SEMI_SLEEPER->value,
            self::DISABLED->value,
            self::VIP->value,
        ];
    }

    public static function getSeatTypeName(string $seatType): string
    {
        return match ($seatType) {
            self::WINDOW->value => 'Window',
            self::AISLE->value => 'Aisle',
            self::MIDDLE->value => 'Middle',
            self::EXECUTIVE->value => 'Executive',
            self::SLEEPER->value => 'Sleeper',
            self::SEMI_SLEEPER->value => 'Semi-Sleeper',
            self::DISABLED->value => 'Disabled Access',
            self::VIP->value => 'VIP',
            default => 'Unknown',
        };
    }

    public static function getSeatTypeColor(string $seatType): string
    {
        return match ($seatType) {
            self::WINDOW->value => 'primary',
            self::AISLE->value => 'secondary',
            self::MIDDLE->value => 'info',
            self::EXECUTIVE->value => 'warning',
            self::SLEEPER->value => 'success',
            self::SEMI_SLEEPER->value => 'light',
            self::DISABLED->value => 'danger',
            self::VIP->value => 'dark',
        };
    }

    public static function getSeatTypeIcon(string $seatType): string
    {
        return match ($seatType) {
            self::WINDOW->value => 'bx-window',
            self::AISLE->value => 'bx-walk',
            self::MIDDLE->value => 'bx-chair',
            self::EXECUTIVE->value => 'bx-crown',
            self::SLEEPER->value => 'bx-bed',
            self::SEMI_SLEEPER->value => 'bx-moon',
            self::DISABLED->value => 'bx-wheelchair',
            self::VIP->value => 'bx-star',
        };
    }

    public static function getSeatTypeDescription(string $seatType): string
    {
        return match ($seatType) {
            self::WINDOW->value => 'Seat next to the window',
            self::AISLE->value => 'Seat next to the aisle',
            self::MIDDLE->value => 'Middle seat between window and aisle',
            self::EXECUTIVE->value => 'Executive class seat with extra space',
            self::SLEEPER->value => 'Sleeping berth for overnight journeys',
            self::SEMI_SLEEPER->value => 'Reclining seat for semi-sleeping',
            self::DISABLED->value => 'Accessible seat for disabled passengers',
            self::VIP->value => 'VIP seat with premium amenities',
        };
    }
}
