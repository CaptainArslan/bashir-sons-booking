<?php

namespace App\Enums;

enum AnnouncementPriorityEnum: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';

    public static function getPriorities(): array
    {
        return [
            self::LOW->value,
            self::MEDIUM->value,
            self::HIGH->value,
        ];
    }

    public static function getPriorityName(string $priority): string
    {
        return match ($priority) {
            self::LOW->value => 'Low',
            self::MEDIUM->value => 'Medium',
            self::HIGH->value => 'High',
        };
    }

    public static function getPriorityColor(string $priority): string
    {
        return match ($priority) {
            self::LOW->value => 'warning',
            self::MEDIUM->value => 'info',
            self::HIGH->value => 'danger',
        };
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getName(): string
    {
        return self::getPriorityName($this->value);
    }
}
