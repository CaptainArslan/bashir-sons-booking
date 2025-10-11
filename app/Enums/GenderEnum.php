<?php

namespace App\Enums;

enum GenderEnum: string
{
    case MALE = 'male';
    case FEMALE = 'female';
    case OTHER = 'other';

    public static function getGenders(): array
    {
        return [
            self::MALE->value,
            self::FEMALE->value,
            self::OTHER->value,
        ];  // Male, Female, Other
    }

    public static function getGenderName(string $gender): string
    {
        return match ($gender) {
            self::MALE->value => 'Male',
            self::FEMALE->value => 'Female',
            self::OTHER->value => 'Other',
            default => 'Unknown',
        };
    }

    public static function getGenderColor(string $gender): string
    {
        return match ($gender) {
            self::MALE->value => 'blue',
            self::FEMALE->value => 'pink',
            self::OTHER->value => 'gray',
        };
    }

    public static function getGenderIcon(string $gender): string
    {
        return match ($gender) {
            self::MALE->value => 'fa-solid fa-mars',
            self::FEMALE->value => 'fa-solid fa-venus',
            self::OTHER->value => 'fa-solid fa-question',
        };
    }

    public static function getGenderDescription(string $gender): string
    {
        return match ($gender) {
            self::MALE->value => 'Male',
            self::FEMALE->value => 'Female',
            self::OTHER->value => 'Other',
        };
    }

    public static function getGenderValue(string $gender): string
    {
        return match ($gender) {
            self::MALE->value => 'male',
            self::FEMALE->value => 'female',
            self::OTHER->value => 'other',
            default => 'unknown',
        };
    }
}
