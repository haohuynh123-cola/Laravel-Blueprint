<?php

declare(strict_types=1);

namespace LaravelBlueprint\Config;

enum StarterKit: string
{
    case None = 'none';
    case Breeze = 'breeze';
    case Jetstream = 'jetstream';
    case Filament = 'filament';

    public function label(): string
    {
        return match ($this) {
            self::None => 'None — bare Laravel',
            self::Breeze => 'Breeze — minimal auth scaffold',
            self::Jetstream => 'Jetstream — teams, 2FA, profile',
            self::Filament => 'Filament — admin panel',
        };
    }
}
