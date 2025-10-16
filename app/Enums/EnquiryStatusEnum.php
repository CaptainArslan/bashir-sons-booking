<?php

namespace App\Enums;

enum EnquiryStatusEnum: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';
    case REJECTED = 'rejected';

    public static function getStatuses(): array
    {
        return [
            self::PENDING->value,
            self::IN_PROGRESS->value,
            self::RESOLVED->value,
            self::CLOSED->value,
            self::REJECTED->value,
        ];
    }

    public static function getStatusName(self $status): string
    {
        return match ($status) {
            self::PENDING => 'Pending',
            self::IN_PROGRESS => 'In Progress',
            self::RESOLVED => 'Resolved',
            self::CLOSED => 'Closed',
            self::REJECTED => 'Rejected',
        };
    }

    public static function getStatusColor(self $status): string
    {
        return match ($status) {
            self::PENDING => 'warning',
            self::IN_PROGRESS => 'info',
            self::RESOLVED => 'success',
            self::CLOSED => 'secondary',
            self::REJECTED => 'danger',
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
