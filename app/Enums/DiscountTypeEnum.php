<?php

namespace App\Enums;

enum DiscountTypeEnum: string
{
    case FLAT = 'flat';
    case PERCENT = 'percent';

    public static function getStatuses(): array
    {
        return [
            self::FLAT->value,
            self::PERCENT->value,
        ];
    }
    
    public static function getStatusName(string $status): string
    {
        return match ($status) {
            self::FLAT->value => 'Flat',
            self::PERCENT->value => 'Percent',
        };
    }
    
    public static function getStatusColor(string $status): string
    {
        return match ($status) {
            self::FLAT->value => 'success',
            self::PERCENT->value => 'warning',
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
        
}
