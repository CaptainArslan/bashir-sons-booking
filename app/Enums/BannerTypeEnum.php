<?php

namespace App\Enums;

enum BannerTypeEnum: string
{
    case MAIN = 'main';
    case INNER = 'inner';
    case SLIDER = 'slider';
    case PROMOTION = 'promotion';
    case OTHER = 'other';

    public static function getTypes(): array
    {
        return [
            self::MAIN->value,
            self::INNER->value,
            self::SLIDER->value,
            self::PROMOTION->value,
            self::OTHER->value
        ];
    }

    public static function getTypeName(string $type): string
    {
        return match ($type) {
            self::MAIN->value => 'Main',
            self::INNER->value => 'Inner',
            self::SLIDER->value => 'Slider',
            self::PROMOTION->value => 'Promotion',
            self::OTHER->value => 'Other',
        };
    }

    public static function getTypeColor(string $type): string
    {
        return match ($type) {
            self::MAIN->value => 'success',
            self::INNER->value => 'warning',
            self::SLIDER->value => 'info',
            self::PROMOTION->value => 'primary',
            self::OTHER->value => 'secondary',
        };
    }
}
