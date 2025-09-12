<?php

declare(strict_types=1);

namespace App\Enums;

enum LinkCategory: string
{
    case READ = 'read';
    case REFERENCE = 'reference';
    case WATCH = 'watch';
    case TOOLS = 'tools';

    public function label(): string
    {
        return match ($this) {
            self::READ => 'Read',
            self::REFERENCE => 'Reference',
            self::WATCH => 'Watch',
            self::TOOLS => 'Tools',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::READ => 'Articles, tutorials, blog posts',
            self::REFERENCE => 'Docs, cheat sheets, specs',
            self::WATCH => 'Videos, courses, demos',
            self::TOOLS => 'Libraries, utilities, SaaS discoveries',
        };
    }
}
