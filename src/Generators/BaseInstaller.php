<?php

declare(strict_types=1);

namespace LaravelBlueprint\Generators;

use LaravelBlueprint\Config\BlueprintConfig;
use LaravelBlueprint\Support\ProcessRunner;

/**
 * Runs `composer create-project laravel/laravel` to lay down the base app.
 *
 * We delegate to the official template rather than reinventing it — this keeps
 * us tracking upstream Laravel automatically.
 */
final readonly class BaseInstaller implements Generator
{
    public function __construct(private ProcessRunner $runner) {}

    public function generate(BlueprintConfig $config): void
    {
        $this->runner->run([
            'composer',
            'create-project',
            '--prefer-dist',
            '--no-interaction',
            'laravel/laravel',
            $config->targetPath,
        ]);
    }
}
