<?php

declare(strict_types=1);

namespace LaravelBlueprint\Config;

enum DockerMode: string
{
    case None = 'none';
    case Sail = 'sail';
    case Production = 'production';
    case Both = 'both';

    public function label(): string
    {
        return match ($this) {
            self::None => 'None',
            self::Sail => 'Sail (development only)',
            self::Production => 'Production Dockerfile only',
            self::Both => 'Sail + production Dockerfile',
        };
    }
}
