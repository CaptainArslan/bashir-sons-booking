<?php

namespace App\Enums;

enum AnnouncementDisplayTypeEnum: string
{
    case BANNER = 'banner';
    case POPUP = 'popup';
    case NOTIFICATION = 'notification';

    public static function getDisplayTypes(): array
    {
        return [
            self::BANNER->value,
            self::POPUP->value,
            self::NOTIFICATION->value,
        ];
    }

    public static function getDisplayName(string $displayType): string
    {
        return match ($displayType) {
            self::BANNER->value => 'Banner',
            self::POPUP->value => 'Popup',
            self::NOTIFICATION->value => 'Notification',
        };
    }

    public static function getDisplayColor(string $displayType): string
    {
        return match ($displayType) {
            self::BANNER->value => 'success',
            self::POPUP->value => 'warning',
            self::NOTIFICATION->value => 'info',
        };
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getName(): string
    {
        return self::getDisplayName($this->value);
    }
}
