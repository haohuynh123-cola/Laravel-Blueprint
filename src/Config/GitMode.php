<?php

declare(strict_types=1);

namespace LaravelBlueprint\Config;

enum GitMode: string
{
    case Skip = 'skip';
    case Init = 'init';
    case Commit = 'commit';

    public function label(): string
    {
        return match ($this) {
            self::Skip => 'Skip',
            self::Init => 'git init only',
            self::Commit => 'git init + initial commit',
        };
    }
}
