<?php

declare(strict_types=1);

namespace LaravelBlueprint\Config;

enum Extra: string
{
    case Horizon = 'horizon';
    case Telescope = 'telescope';
    case Pulse = 'pulse';
    case Octane = 'octane';
    case Scout = 'scout';
    case Sanctum = 'sanctum';
    case Pint = 'pint';
    case Larastan = 'larastan';
    case Dusk = 'dusk';
    case Sail = 'sail';

    public function label(): string
    {
        return match ($this) {
            self::Horizon => 'Horizon — Redis queue dashboard',
            self::Telescope => 'Telescope — request/query debugger (dev)',
            self::Pulse => 'Pulse — application performance dashboard',
            self::Octane => 'Octane — high-performance app server',
            self::Scout => 'Scout — full-text search',
            self::Sanctum => 'Sanctum — API tokens / SPA auth',
            self::Pint => 'Pint — opinionated PHP code style fixer',
            self::Larastan => 'Larastan — PHPStan for Laravel',
            self::Dusk => 'Dusk — browser tests',
            self::Sail => 'Sail — Docker dev environment',
        };
    }
}
