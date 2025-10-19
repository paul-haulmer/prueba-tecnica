<?php

namespace App\Enums;

enum PackagePriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';

    /**
     * Return all supported values as array.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $priority): string => $priority->value, self::cases());
    }
}
