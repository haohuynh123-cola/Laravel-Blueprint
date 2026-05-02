<?php

declare(strict_types=1);

namespace LaravelBlueprint\Config;

enum Cache: string
{
    case Database = 'database';
    case File = 'file';
    case Redis = 'redis';
    case Memcached = 'memcached';

    public function label(): string
    {
        return match ($this) {
            self::Database => 'Database (zero setup)',
            self::File => 'File',
            self::Redis => 'Redis',
            self::Memcached => 'Memcached',
        };
    }
}
