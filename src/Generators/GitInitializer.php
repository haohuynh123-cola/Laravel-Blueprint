<?php

declare(strict_types=1);

namespace LaravelBlueprint\Generators;

use LaravelBlueprint\Config\BlueprintConfig;
use LaravelBlueprint\Config\GitMode;
use LaravelBlueprint\Support\ProcessRunner;

final readonly class GitInitializer implements Generator
{
    public function __construct(private ProcessRunner $runner) {}

    public function generate(BlueprintConfig $config): void
    {
        if ($config->gitMode === GitMode::Skip) {
            return;
        }

        $this->runner->run(['git', 'init', '-b', 'main'], cwd: $config->targetPath);

        if ($config->gitMode === GitMode::Commit) {
            $this->runner->run(['git', 'add', '.'], cwd: $config->targetPath);
            $this->runner->run(
                ['git', 'commit', '-m', 'chore: initial commit from laravel-blueprint'],
                cwd: $config->targetPath,
            );
        }
    }
}
