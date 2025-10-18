<?php

namespace App\Enums;

enum BookingChannelEnum: string
{
    case ONLINE = 'online';
    case COUNTER = 'counter';
    case CALL_CENTER = 'call_center';
    case AGENT = 'agent';

    public static function getChannels(): array
    {
        return [
            self::ONLINE->value,
            self::CALL_CENTER->value,
            self::AGENT->value,
        ];
    }

    public static function getChannelName(string $channel): string
    {
        return match ($channel) {
            self::ONLINE->value => 'Online',
            self::COUNTER->value => 'Counter',
            self::CALL_CENTER->value => 'Call Center',
            self::AGENT->value => 'Agent',
        };
    }
    public static function getChannelColor(string $channel): string
    {
        return match ($channel) {
            self::ONLINE->value => 'success',
            self::COUNTER->value => 'secondary',
            self::CALL_CENTER->value => 'info',
            self::AGENT->value => 'primary',
        };
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getName(): string
    {
        return self::getChannelName($this->value);
    }

    public function getColor(): string
    {
        return self::getChannelColor($this->value);
    }
}
