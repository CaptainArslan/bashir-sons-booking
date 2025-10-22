<?php

namespace App\Enums;

enum PlatformEnum: string
{
    case ANDROID = 'android';
    case IOS = 'ios';
    case WEB = 'web';
    case COUNTER = 'counter';

    public static function getPlatforms(): array
    {
        return [
            self::ANDROID->value,
            self::IOS->value,
            self::WEB->value,
            self::COUNTER->value,
        ];
    }

    public static function getPlatformName(string $platform): string
    {
        return match ($platform) {
            self::ANDROID->value => 'Android',
            self::IOS->value => 'iOS',
            self::WEB->value => 'Web',
            self::COUNTER->value => 'Counter',
        };
    }

    public static function getPlatformColor(string $platform): string
    {
        return match ($platform) {
            self::ANDROID->value => 'success',
            self::IOS->value => 'primary',
            self::WEB->value => 'info',
            self::COUNTER->value => 'warning',
        };
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getName(): string
    {
        return self::getPlatformName($this->value);
    }

    public function getColor(): string
    {
        return self::getPlatformColor($this->value);
    }
}
