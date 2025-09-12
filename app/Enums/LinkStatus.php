<?php

declare(strict_types=1);

namespace App\Enums;

enum LinkStatus: string
{
    case UNREAD = 'unread';
    case READING = 'reading';
    case READ = 'read';
    case REFERENCE = 'reference';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::UNREAD => 'Unread',
            self::READING => 'Reading',
            self::READ => 'Read',
            self::REFERENCE => 'Reference',
            self::ARCHIVED => 'Archived',
        };
    }

    public function isActive(): bool
    {
        return ! in_array($this, [self::ARCHIVED]);
    }

    public function canTransitionTo(LinkStatus $status): bool
    {
        return match ($this) {
            self::UNREAD => in_array($status, [self::READING, self::READ, self::REFERENCE, self::ARCHIVED]),
            self::READING => in_array($status, [self::READ, self::REFERENCE, self::ARCHIVED]),
            self::READ => in_array($status, [self::REFERENCE, self::ARCHIVED]),
            self::REFERENCE => in_array($status, [self::ARCHIVED]),
            self::ARCHIVED => in_array($status, [self::UNREAD]),
        };
    }
}
