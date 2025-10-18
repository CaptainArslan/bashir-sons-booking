<?php

namespace App\Enums;

enum PaymentStatusEnum: string
{

    case PENDING = 'pending';
    case SUCCESS = 'success';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';

    public static function getStatuses(): array
    {
        return [
            self::PENDING->value,
            self::SUCCESS->value,
            self::FAILED->value,
            self::REFUNDED->value,
        ];
    }

    public static function getStatusName(string $status): string
    {
        return match ($status) {
            self::PENDING->value => 'Pending',
            self::SUCCESS->value => 'Success',
            self::FAILED->value => 'Failed',
            self::REFUNDED->value => 'Refunded',
        };
    }

    public static function getStatusColor(string $status): string
    {
        return match ($status) {
            self::PENDING->value => 'warning',
            self::SUCCESS->value => 'success',
            self::FAILED->value => 'danger',
            self::REFUNDED->value => 'secondary',
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

    public function getColor(): string
    {
        return self::getStatusColor($this->value);
    }
}
