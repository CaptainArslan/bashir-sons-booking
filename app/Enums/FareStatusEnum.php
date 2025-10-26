<?php

namespace App\Enums;

enum FareStatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    public function getLabel(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::INACTIVE => 'secondary',
        };
    }

    public function getBadgeClass(): string
    {
        return match ($this) {
            self::ACTIVE => 'bg-success',
            self::INACTIVE => 'bg-secondary',
        };
    }

    public static function getStatuses(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getStatusOptions(): array
    {
        $options = [];
        foreach (self::cases() as $status) {
            $options[$status->value] = $status->getLabel();
        }

        return $options;
    }

    public static function getStatusBadge(string $status): string
    {
        $enum = self::from($status);
        $badgeClass = $enum->getBadgeClass();
        $label = $enum->getLabel();

        return '<span class="badge '.$badgeClass.'">'.$label.'</span>';
    }

    public static function getStatusName(string $status): string
    {
        return match ($status) {
            self::ACTIVE->value => 'Active',
            self::INACTIVE->value => 'Inactive',
            default => 'Unknown',
        };
    }

    public static function getStatusColor(string $status): string
    {
        return match ($status) {
            self::ACTIVE->value => 'success',
            self::INACTIVE->value => 'warning',
            default => 'unknown',
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
