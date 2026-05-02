<?php

declare(strict_types=1);

namespace LaravelBlueprint\Generators;

use LaravelBlueprint\Config\BlueprintConfig;
use LaravelBlueprint\Config\StarterKit;
use LaravelBlueprint\Config\TestRunner;
use LaravelBlueprint\Support\ProcessRunner;

/**
 * Installs the chosen Laravel starter kit on top of the base install.
 *
 * Each kit goes through `composer require` followed by its `artisan *:install`
 * command. Flags are passed to keep the install non-interactive.
 */
final readonly class StarterKitGenerator implements Generator
{
    public function __construct(private ProcessRunner $runner) {}

    public function generate(BlueprintConfig $config): void
    {
        match ($config->starterKit) {
            StarterKit::None => null,
            StarterKit::Breeze => $this->installBreeze($config),
            StarterKit::Jetstream => $this->installJetstream($config),
            StarterKit::Filament => $this->installFilament($config),
        };
    }

    private function installBreeze(BlueprintConfig $config): void
    {
        $this->runner->run(
            ['composer', 'require', 'laravel/breeze', '--dev', '--no-interaction'],
            cwd: $config->targetPath,
        );

        $args = [
            'php',
            'artisan',
            'breeze:install',
            $config->frontendStack->breezeStack(),
            '--no-interaction',
        ];

        if ($config->testRunner === TestRunner::Pest) {
            $args[] = '--pest';
        }

        $this->runner->run($args, cwd: $config->targetPath);
    }

    private function installJetstream(BlueprintConfig $config): void
    {
        $this->runner->run(
            ['composer', 'require', 'laravel/jetstream', '--no-interaction'],
            cwd: $config->targetPath,
        );

        // Jetstream supports livewire or inertia stacks only.
        $stack = $config->frontendStack->breezeStack();
        $jetstreamStack = $stack === 'livewire' ? 'livewire' : 'inertia';

        $args = ['php', 'artisan', 'jetstream:install', $jetstreamStack, '--no-interaction'];
        if ($config->testRunner === TestRunner::Pest) {
            $args[] = '--pest';
        }

        $this->runner->run($args, cwd: $config->targetPath);
    }

    private function installFilament(BlueprintConfig $config): void
    {
        $this->runner->run(
            ['composer', 'require', 'filament/filament:^3.0', '--no-interaction'],
            cwd: $config->targetPath,
        );

        $this->runner->run(
            ['php', 'artisan', 'filament:install', '--panels', '--no-interaction'],
            cwd: $config->targetPath,
        );
    }
}
