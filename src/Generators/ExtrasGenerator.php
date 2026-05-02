<?php

declare(strict_types=1);

namespace LaravelBlueprint\Generators;

use LaravelBlueprint\Config\BlueprintConfig;
use LaravelBlueprint\Config\Extra;
use LaravelBlueprint\Support\ProcessRunner;

/**
 * Installs every chosen extra by running `composer require` and an optional
 * follow-up `php artisan` install command.
 *
 * Each entry in EXTRA_RECIPES is a small data record so adding a new extra is
 * one enum case + one array entry — no orchestration logic to touch.
 */
final readonly class ExtrasGenerator implements Generator
{
    /**
     * @var array<string, array{
     *     packages: list<string>,
     *     dev: bool,
     *     install: list<list<string>>
     * }>
     */
    private const EXTRA_RECIPES = [
        'horizon' => [
            'packages' => ['laravel/horizon'],
            'dev' => false,
            'install' => [['php', 'artisan', 'horizon:install']],
        ],
        'telescope' => [
            'packages' => ['laravel/telescope'],
            'dev' => true,
            'install' => [['php', 'artisan', 'telescope:install']],
        ],
        'pulse' => [
            'packages' => ['laravel/pulse'],
            'dev' => false,
            'install' => [
                ['php', 'artisan', 'vendor:publish', '--provider=Laravel\\Pulse\\PulseServiceProvider'],
            ],
        ],
        'octane' => [
            'packages' => ['laravel/octane'],
            'dev' => false,
            'install' => [['php', 'artisan', 'octane:install', '--server=frankenphp']],
        ],
        'scout' => [
            'packages' => ['laravel/scout'],
            'dev' => false,
            'install' => [
                ['php', 'artisan', 'vendor:publish', '--provider=Laravel\\Scout\\ScoutServiceProvider'],
            ],
        ],
        'sanctum' => [
            'packages' => ['laravel/sanctum'],
            'dev' => false,
            'install' => [['php', 'artisan', 'install:api', '--no-interaction']],
        ],
        'pint' => [
            'packages' => ['laravel/pint'],
            'dev' => true,
            'install' => [],
        ],
        'larastan' => [
            'packages' => ['larastan/larastan'],
            'dev' => true,
            'install' => [],
        ],
        'dusk' => [
            'packages' => ['laravel/dusk'],
            'dev' => true,
            'install' => [['php', 'artisan', 'dusk:install']],
        ],
        // Sail is handled by DockerGenerator — installing it twice from
        // ExtrasGenerator would just be wasted composer time, so we skip.
        'sail' => [
            'packages' => [],
            'dev' => true,
            'install' => [],
        ],
    ];

    public function __construct(private ProcessRunner $runner) {}

    public function generate(BlueprintConfig $config): void
    {
        foreach ($config->extras as $extra) {
            $this->applyRecipe($extra, $config);
        }
    }

    private function applyRecipe(Extra $extra, BlueprintConfig $config): void
    {
        $recipe = self::EXTRA_RECIPES[$extra->value] ?? null;
        if ($recipe === null || $recipe['packages'] === []) {
            return;
        }

        $require = ['composer', 'require', ...$recipe['packages'], '--no-interaction'];
        if ($recipe['dev']) {
            $require[] = '--dev';
        }

        $this->runner->run($require, cwd: $config->targetPath);

        foreach ($recipe['install'] as $command) {
            $this->runner->run($command, cwd: $config->targetPath);
        }
    }
}
