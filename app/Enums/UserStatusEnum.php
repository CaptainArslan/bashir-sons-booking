<?php

namespace App\Enums;

enum UserStatusEnum: string
{
    case ACTIVE = 'active';
    case BANNED = 'banned';

    public static function getStatuses(): array
    {
        return [
            self::ACTIVE->value,
            self::BANNED->value,
        ];
    }

    public static function getStatusName(string $status): string
    {
        return match ($status) {
            self::ACTIVE->value => 'Active',
            self::BANNED->value => 'Banned',
            default => 'Unknown',
        };
    }

    public static function getStatusColor(string $status): string
    {
        return match ($status) {
            self::ACTIVE->value => 'success',
            self::BANNED->value => 'danger',
            default => 'secondary',
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
