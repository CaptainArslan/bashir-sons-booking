<?php

namespace App\Enums;

enum PaymentMethodEnum: string
{
    case CASH = 'cash';
    case CARD = 'card';
    case MOBILE_WALLET = 'mobile_wallet';
    case BANK_TRANSFER = 'bank_transfer';
    case OTHER = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::CASH => 'Cash',
            self::CARD => 'Credit / Debit Card',
            self::MOBILE_WALLET => 'Mobile Wallet (e.g., JazzCash, Easypaisa)',
            self::BANK_TRANSFER => 'Bank Transfer',
            self::OTHER => 'Other',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::CASH => 'success',
            self::CARD => 'primary',
            self::MOBILE_WALLET => 'info',
            self::BANK_TRANSFER => 'warning',
            self::OTHER => 'secondary',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::CASH => 'fa-solid fa-money-bill-wave',
            self::CARD => 'fa-solid fa-credit-card',
            self::MOBILE_WALLET => 'fa-solid fa-mobile-screen-button',
            self::BANK_TRANSFER => 'fa-solid fa-building-columns',
            self::OTHER => 'fa-solid fa-question-circle',
        };
    }

    public function getBadge(): string
    {
        return "badge bg-{$this->getColor()}";
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[] = [
                'value' => $case->value,
                'label' => $case->getLabel(),
                'icon' => $case->getIcon(),
                'badge' => $case->getBadge(),
                'color' => $case->getColor(),
            ];
        }

        return $options;
    }
}
