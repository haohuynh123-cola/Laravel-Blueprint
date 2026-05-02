<?php

declare(strict_types=1);

namespace LaravelBlueprint\Config;

enum FrontendStack: string
{
    case Blade = 'blade';
    case Livewire = 'livewire';
    case InertiaVue = 'inertia-vue';
    case InertiaReact = 'inertia-react';
    case Api = 'api';
    case None = 'none';

    public function label(): string
    {
        return match ($this) {
            self::Blade => 'Blade with Alpine',
            self::Livewire => 'Livewire',
            self::InertiaVue => 'Inertia + Vue',
            self::InertiaReact => 'Inertia + React',
            self::Api => 'API only (no frontend)',
            self::None => 'None',
        };
    }

    /**
     * Maps to the breeze:install stack flag.
     */
    public function breezeStack(): string
    {
        return match ($this) {
            self::Blade => 'blade',
            self::Livewire => 'livewire',
            self::InertiaVue => 'vue',
            self::InertiaReact => 'react',
            self::Api => 'api',
            self::None => 'blade',
        };
    }
}
