<?php

namespace App\Enums;

enum ExpenseTypeEnum: string
{
    case Fuel = 'fuel';
    case Toll = 'toll';
    case DriverPay = 'driver_pay';
    case Maintenance = 'maintenance';
    case Refreshment = 'refreshment';
    case Parking = 'parking';
    case Miscellaneous = 'misc';

    public function label(): string
    {
        return match ($this) {
            self::Fuel => 'Fuel',
            self::Toll => 'Toll',
            self::DriverPay => 'Driver Pay',
            self::Maintenance => 'Maintenance',
            self::Refreshment => 'Refreshment',
            self::Parking => 'Parking',
            self::Miscellaneous => 'Miscellaneous',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Fuel => 'red',
            self::Toll => 'blue',
            self::DriverPay => 'green',
            self::Maintenance => 'yellow',
            self::Refreshment => 'purple',
            self::Parking => 'indigo',
            self::Miscellaneous => 'gray',
        };
    }

    public function requiresReceipt(): bool
    {
        return in_array($this, [self::Fuel, self::Toll, self::Maintenance]);
    }
}
