<?php

declare(strict_types=1);

namespace LaravelBlueprint\Config;

enum TestRunner: string
{
    case Pest = 'pest';
    case PhpUnit = 'phpunit';

    public function label(): string
    {
        return match ($this) {
            self::Pest => 'Pest',
            self::PhpUnit => 'PHPUnit',
        };
    }
}
