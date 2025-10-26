<?php

namespace App\Enums;

enum AnnouncementAudienceTypeEnum: string
{
    case ALL = 'all';
    case ROLES = 'roles';
    case USERS = 'users';

    public static function getAudienceTypes(): array
    {
        return [
            self::ALL->value,
            self::ROLES->value,
            self::USERS->value,
        ];
    }

    public static function getAudienceTypeName(string $audienceType): string
    {
        return match ($audienceType) {
            self::ALL->value => 'All',
            self::ROLES->value => 'Roles',
            self::USERS->value => 'Users',
        };
    }

    public static function getAudienceTypeColor(string $audienceType): string
    {
        return match ($audienceType) {
            self::ALL->value => 'success',
            self::ROLES->value => 'warning',
            self::USERS->value => 'info',
        };
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getName(): string
    {
        return self::getAudienceTypeName($this->value);
    }
}
