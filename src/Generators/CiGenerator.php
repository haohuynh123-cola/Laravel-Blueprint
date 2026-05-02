<?php

declare(strict_types=1);

namespace LaravelBlueprint\Generators;

use LaravelBlueprint\Config\BlueprintConfig;
use LaravelBlueprint\Config\CiPreset;
use LaravelBlueprint\Config\TestRunner;
use LaravelBlueprint\Support\StubLoader;

/**
 * Writes the chosen CI workflow files into .github/workflows/.
 */
final readonly class CiGenerator implements Generator
{
    public function __construct(private StubLoader $stubs = new StubLoader()) {}

    public function generate(BlueprintConfig $config): void
    {
        if ($config->ciPreset === CiPreset::None) {
            return;
        }

        $base = $config->targetPath.'/.github/workflows';

        $this->stubs->write(
            'ci/tests.yml.stub',
            $base.'/tests.yml',
            [
                'project_name' => $config->projectName,
                'test_runner' => $config->testRunner->label(),
                'test_command' => $this->testCommand($config->testRunner),
            ],
        );

        $this->stubs->write(
            'ci/lint.yml.stub',
            $base.'/lint.yml',
            ['project_name' => $config->projectName],
        );
    }

    private function testCommand(TestRunner $runner): string
    {
        return match ($runner) {
            TestRunner::Pest => 'vendor/bin/pest',
            TestRunner::PhpUnit => 'vendor/bin/phpunit',
        };
    }
}
