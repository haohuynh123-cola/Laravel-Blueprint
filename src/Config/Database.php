<?php

declare(strict_types=1);

namespace LaravelBlueprint\Config;

enum Database: string
{
    case MySQL = 'mysql';
    case PostgreSQL = 'pgsql';
    case SQLite = 'sqlite';
    case MariaDB = 'mariadb';

    public function label(): string
    {
        return match ($this) {
            self::MySQL => 'MySQL',
            self::PostgreSQL => 'PostgreSQL',
            self::SQLite => 'SQLite',
            self::MariaDB => 'MariaDB',
        };
    }

    public function defaultPort(): ?int
    {
        return match ($this) {
            self::MySQL, self::MariaDB => 3306,
            self::PostgreSQL => 5432,
            self::SQLite => null,
        };
    }
}
