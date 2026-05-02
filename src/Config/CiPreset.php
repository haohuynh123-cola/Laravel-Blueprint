<?php

declare(strict_types=1);

namespace LaravelBlueprint\Config;

enum CiPreset: string
{
    case None = 'none';
    case GitHubActions = 'github-actions';

    public function label(): string
    {
        return match ($this) {
            self::None => 'None',
            self::GitHubActions => 'GitHub Actions',
        };
    }
}
