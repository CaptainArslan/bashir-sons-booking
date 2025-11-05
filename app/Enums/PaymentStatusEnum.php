<?php

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case UNPAID = 'unpaid';
    case PAID = 'paid';
    case REFUNDED = 'refunded';
    case FAILED = 'failed';

    /**
     * Human-readable label for UI display.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::UNPAID => 'Unpaid',
            self::PAID => 'Paid',
            self::REFUNDED => 'Refunded',
            self::FAILED => 'Failed',
        };
    }

    /**
     * Bootstrap-compatible color name.
     */
    public function getColor(): string
    {
        return match ($this) {
            self::UNPAID => 'warning',
            self::PAID => 'success',
            self::REFUNDED => 'secondary',
            self::FAILED => 'danger',
        };
    }

    /**
     * Font Awesome icon for quick visual cues.
     */
    public function getIcon(): string
    {
        return match ($this) {
            self::UNPAID => 'fa-solid fa-clock',
            self::PAID => 'fa-solid fa-check-circle',
            self::REFUNDED => 'fa-solid fa-rotate-left',
            self::FAILED => 'fa-solid fa-times-circle',
        };
    }

    /**
     * Bootstrap badge CSS class.
     */
    public function getBadge(): string
    {
        return "badge bg-{$this->getColor()}";
    }

    /**
     * Returns all enum values as a simple array.
     */
    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Returns a structured array for dropdowns or JSON APIs.
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[] = [
                'value' => $case->value,
                'label' => $case->getLabel(),
                'color' => $case->getColor(),
                'icon' => $case->getIcon(),
                'badge' => $case->getBadge(),
            ];
        }

        return $options;
    }
}
