<?php

namespace App\Enums;

enum BookingTypeEnum: string
{
    case ONLINE = 'online';
    case COUNTER = 'counter';
    case PHONE = 'phone';

    public static function getBookingTypes(): array
    {
        return [
            self::ONLINE->value,
            self::COUNTER->value,
            self::PHONE->value,
        ];
    }

    public static function getBookingTypeName(string $bookingType): string
    {
        return match ($bookingType) {
            self::ONLINE->value => 'Online',
            self::COUNTER->value => 'Counter',
            self::PHONE->value => 'Phone',
        };
    }

    public static function getBookingTypeColor(string $bookingType): string
    {
        return match ($bookingType) {
            self::ONLINE->value => 'primary',
            self::COUNTER->value => 'secondary',
            self::PHONE->value => 'info',
        };
    }

    public static function getBookingTypeIcon(string $bookingType): string
    {
        return match ($bookingType) {
            self::ONLINE->value => 'fa-solid fa-globe',
            self::COUNTER->value => 'fa-solid fa-store',
            self::PHONE->value => 'fa-solid fa-phone',
        };
    }

    public static function getBookingTypeDescription(string $bookingType): string
    {
        return match ($bookingType) {
            self::ONLINE->value => 'Booking made online',
            self::COUNTER->value => 'Booking made at a counter',
            self::PHONE->value => 'Booking made over the phone',
        };
    }
}
