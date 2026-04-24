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

    public function badgeClasses(): string
    {
        return match ($this) {
            self::READ => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
            self::REFERENCE => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
            self::WATCH => 'bg-pink-100 text-pink-700 dark:bg-pink-900/40 dark:text-pink-300',
            self::TOOLS => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function badgeClassMap(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $c) => [$c->value => $c->badgeClasses()])
            ->all();
    }
}
