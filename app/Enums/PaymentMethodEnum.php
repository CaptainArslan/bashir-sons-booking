<?php

namespace App\Enums;

enum PaymentMethodEnum: string
{
    case ONLINE = 'online';
    case CASH = 'cash';
    case CHECK = 'check';
    case OTHER = 'other';

    public static function getMethods(): array
    {
        return [
            self::ONLINE->value,
            self::CASH->value,
            self::CHECK->value,
            self::OTHER->value,
        ];
    }
    public static function getMethodName(string $method): string
    {
        return match ($method) {
            self::ONLINE->value => 'Online',
            self::CASH->value => 'Cash',
            self::CHECK->value => 'Check',
            self::OTHER->value => 'Other',
        };
    }
    public static function getMethodColor(string $method): string
    {
        return match ($method) {
            self::ONLINE->value => 'success',
            self::CASH->value => 'secondary',
            self::CHECK->value => 'info',
            self::OTHER->value => 'danger',
        };
    }
    public function getValue(): string
    {
        return $this->value;
    }

    public function getName(): string
    {
        return self::getMethodName($this->value);
    }

    public function getColor(): string
    {
        return self::getMethodColor($this->value);
    }
}
