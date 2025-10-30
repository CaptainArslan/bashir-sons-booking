<?php

namespace App\Enums;

enum ChannelEnum: string
{
    case ONLINE = 'online';
    case COUNTER = 'counter';
    case PHONE = 'phone';

    public function getLabel(): string
    {
        return match ($this) {
            self::ONLINE => 'Online',
            self::COUNTER => 'Counter',
            self::PHONE => 'Phone',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ONLINE => 'success',
            self::COUNTER => 'primary',
            self::PHONE => 'info',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::ONLINE => 'fa-solid fa-globe',
            self::COUNTER => 'fa-solid fa-store',
            self::PHONE => 'fa-solid fa-phone',
        };
    }

    public function getBadge(): string
    {
        return match ($this) {
            self::ONLINE => "badge bg-{$this->getColor()}",
            self::COUNTER => "badge bg-{$this->getColor()}",
            self::PHONE => "badge bg-{$this->getColor()}",
        };
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
