<?php

namespace App\Enums;

enum ExpenseTypeEnum: string
{
    case COMMISSION = 'commission';
    case GHAKRI = 'ghakri';
    case FUEL = 'fuel';
    case MAINTENANCE = 'maintenance';
    case TOLL = 'toll';
    case DRIVER_ALLOWANCE = 'driver_allowance';
    case CLEANING = 'cleaning';
    case OTHER = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::COMMISSION => 'Commission',
            self::GHAKRI => 'Ghakri',
            self::FUEL => 'Fuel',
            self::MAINTENANCE => 'Maintenance',
            self::TOLL => 'Toll',
            self::DRIVER_ALLOWANCE => 'Driver Allowance',
            self::CLEANING => 'Cleaning',
            self::OTHER => 'Other',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::COMMISSION => 'primary',
            self::GHAKRI => 'info',
            self::FUEL => 'warning',
            self::MAINTENANCE => 'danger',
            self::TOLL => 'secondary',
            self::DRIVER_ALLOWANCE => 'success',
            self::CLEANING => 'dark',
            self::OTHER => 'secondary',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::COMMISSION => 'fa-solid fa-hand-holding-dollar',
            self::GHAKRI => 'fa-solid fa-money-bill-transfer',
            self::FUEL => 'fa-solid fa-gas-pump',
            self::MAINTENANCE => 'fa-solid fa-wrench',
            self::TOLL => 'fa-solid fa-road',
            self::DRIVER_ALLOWANCE => 'fa-solid fa-user-tie',
            self::CLEANING => 'fa-solid fa-spray-can',
            self::OTHER => 'fa-solid fa-receipt',
        };
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[] = [
                'value' => $case->value,
                'label' => $case->getLabel(),
                'icon' => $case->getIcon(),
                'color' => $case->getColor(),
            ];
        }

        return $options;
    }
}
