<?php

namespace App\Enums;

enum FrequencyTypeEnum: string
{
    case DAILY = 'daily';
    case WEEKDAYS = 'weekdays';
    case WEEKENDS = 'weekends';
    case CUSTOM = 'custom';

    public static function getFrequencyTypeValues(): array
    {
        return [
            self::DAILY->value,
            self::WEEKDAYS->value,
            self::WEEKENDS->value,
            self::CUSTOM->value,
        ];
    }

    public static function getFrequencyTypeName(string $frequencyType): string
    {
        return match ($frequencyType) {
            self::DAILY->value => 'Daily',
            self::WEEKDAYS->value => 'Weekdays',
            self::WEEKENDS->value => 'Weekends',
            self::CUSTOM->value => 'Custom',
        };
    }

    public static function getFrequencyTypeColor(string $frequencyType): string
    {
        return match ($frequencyType) {
            self::DAILY->value => 'success',
            self::WEEKDAYS->value => 'warning',
            self::WEEKENDS->value => 'info',
            self::CUSTOM->value => 'primary',
        };
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getName(): string
    {
        return self::getFrequencyTypeName($this->value);
    }
}
