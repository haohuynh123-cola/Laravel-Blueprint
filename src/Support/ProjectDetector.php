<?php

declare(strict_types=1);

namespace LaravelBlueprint\Support;

use RuntimeException;

/**
 * Detects whether a directory contains a Laravel project and extracts the
 * project name. Used by `blueprint add` to validate the cwd before applying
 * any layer.
 */
final class ProjectDetector
{
    public function __construct(private readonly string $path) {}

    public function isLaravelProject(): bool
    {
        $artisan = $this->path.'/artisan';
        $composer = $this->path.'/composer.json';

        if (! is_file($artisan) || ! is_file($composer)) {
            return false;
        }

        $manifest = $this->readComposerJson();

        return isset($manifest['require']['laravel/framework']);
    }

    /**
     * @return array<string, mixed>
     */
    public function readComposerJson(): array
    {
        $composer = $this->path.'/composer.json';

        if (! is_file($composer)) {
            throw new RuntimeException("composer.json not found at $composer");
        }

        $contents = (string) file_get_contents($composer);
        $decoded = json_decode($contents, associative: true);

        if (! is_array($decoded)) {
            throw new RuntimeException("Invalid JSON in $composer");
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    public function projectName(): string
    {
        if ($this->isLaravelProject()) {
            $manifest = $this->readComposerJson();
            $name = $manifest['name'] ?? null;
            if (is_string($name) && $name !== '') {
                // Composer names look like "vendor/project" — keep the project segment.
                $segments = explode('/', $name);
                $tail = end($segments);

                return $tail !== false ? $tail : basename($this->path);
            }
        }

        return basename($this->path);
    }

    public function hasComposerPackage(string $package): bool
    {
        $manifest = $this->readComposerJson();
        $require = $manifest['require'] ?? [];
        $requireDev = $manifest['require-dev'] ?? [];

        return (is_array($require) && isset($require[$package]))
            || (is_array($requireDev) && isset($requireDev[$package]));
    }
}
