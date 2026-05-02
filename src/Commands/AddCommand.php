<?php

declare(strict_types=1);

namespace LaravelBlueprint\Commands;

use LaravelBlueprint\Config\BlueprintConfig;
use LaravelBlueprint\Config\CiPreset;
use LaravelBlueprint\Config\Database;
use LaravelBlueprint\Config\DockerMode;
use LaravelBlueprint\Config\Extra;
use LaravelBlueprint\Config\FrontendStack;
use LaravelBlueprint\Config\GitMode;
use LaravelBlueprint\Config\StarterKit;
use LaravelBlueprint\Config\TestRunner;
use LaravelBlueprint\Generators\CiGenerator;
use LaravelBlueprint\Generators\DockerGenerator;
use LaravelBlueprint\Generators\ExtrasGenerator;
use LaravelBlueprint\Support\ProcessRunner;
use LaravelBlueprint\Support\ProjectDetector;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

/**
 * Apply one or more Blueprint layers to the existing Laravel project in cwd.
 *
 * Designed for brownfield use: drop into a project that was created with
 * `laravel new` years ago, run `blueprint add --docker=production --ci=github-actions`,
 * and you get the same artefacts as a fresh `blueprint new` would have produced.
 *
 * Refuses to touch:
 * - Directories that are not Laravel projects (no artisan + composer.json with laravel/framework)
 * - Existing Docker / CI files unless --force is passed (so we never silently overwrite tuned configs)
 */
#[AsCommand(name: 'add', description: 'Add a Blueprint layer to an existing Laravel project')]
final class AddCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('extra', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Repeatable extras to install')
            ->addOption('docker', null, InputOption::VALUE_REQUIRED, 'Docker mode: none|sail|production|both')
            ->addOption('ci', null, InputOption::VALUE_REQUIRED, 'CI preset: none|github-actions')
            ->addOption('database', null, InputOption::VALUE_REQUIRED, 'Database (used by Sail install)')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Overwrite existing Docker / CI files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            intro('Laravel Blueprint — apply layers to existing project');

            $cwd = getcwd();
            if ($cwd === false) {
                throw new RuntimeException('Could not determine current directory');
            }

            $detector = new ProjectDetector($cwd);
            if (! $detector->isLaravelProject()) {
                throw new RuntimeException(
                    'Not a Laravel project (missing artisan or laravel/framework in composer.json). '
                    .'Run `blueprint add` from your project root.',
                );
            }

            $config = $this->buildConfig($input, $detector, $cwd);

            if ($this->isNoop($config)) {
                throw new RuntimeException('Nothing to add. Pass at least one of --extra, --docker, --ci.');
            }

            $this->guardExistingFiles($config, (bool) $input->getOption('force'));
            $this->runGenerators($config, $output);

            outro("Layers applied to {$config->projectName}");

            return Command::SUCCESS;
        } catch (RuntimeException $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');

            return Command::FAILURE;
        }
    }

    private function buildConfig(InputInterface $input, ProjectDetector $detector, string $cwd): BlueprintConfig
    {
        $extras = $this->parseExtras($input->getOption('extra'));
        $database = $this->parseEnum(Database::class, $input->getOption('database'), Database::SQLite, '--database');
        $docker = $this->parseEnum(DockerMode::class, $input->getOption('docker'), DockerMode::None, '--docker');
        $ci = $this->parseEnum(CiPreset::class, $input->getOption('ci'), CiPreset::None, '--ci');

        return new BlueprintConfig(
            projectName: $detector->projectName(),
            targetPath: $cwd,
            // The fields below are unused by the Add path but the config needs values.
            starterKit: StarterKit::None,
            frontendStack: FrontendStack::None,
            database: $database,
            testRunner: TestRunner::Pest,
            extras: $extras,
            dockerMode: $docker,
            ciPreset: $ci,
            gitMode: GitMode::Skip,
        );
    }

    private function isNoop(BlueprintConfig $config): bool
    {
        return $config->extras === []
            && $config->dockerMode === DockerMode::None
            && $config->ciPreset === CiPreset::None;
    }

    private function guardExistingFiles(BlueprintConfig $config, bool $force): void
    {
        if ($force) {
            return;
        }

        $base = $config->targetPath;
        $candidates = [];

        if (in_array($config->dockerMode, [DockerMode::Production, DockerMode::Both], true)) {
            $candidates[] = $base.'/Dockerfile';
            $candidates[] = $base.'/docker/nginx.conf';
        }
        if ($config->ciPreset === CiPreset::GitHubActions) {
            $candidates[] = $base.'/.github/workflows/tests.yml';
            $candidates[] = $base.'/.github/workflows/lint.yml';
        }

        $existing = array_filter($candidates, 'is_file');

        if ($existing !== []) {
            $rel = array_map(fn (string $p): string => str_replace($base.'/', '', $p), array_values($existing));
            throw new RuntimeException(
                "Refusing to overwrite existing files: ".implode(', ', $rel)
                .". Re-run with --force to replace them.",
            );
        }
    }

    private function runGenerators(BlueprintConfig $config, OutputInterface $output): void
    {
        $runner = new ProcessRunner($output);

        if ($config->extras !== []) {
            info('Installing extras…');
            (new ExtrasGenerator($runner))->generate($config);
        }

        if ($config->dockerMode !== DockerMode::None) {
            info("Configuring Docker ({$config->dockerMode->value})…");
            (new DockerGenerator($runner))->generate($config);
        }

        if ($config->ciPreset !== CiPreset::None) {
            info("Adding CI ({$config->ciPreset->value})…");
            (new CiGenerator())->generate($config);
        }
    }

    /**
     * @template T of \BackedEnum
     *
     * @param  class-string<T>  $enum
     * @param  T  $default
     * @return T
     */
    private function parseEnum(string $enum, mixed $raw, \BackedEnum $default, string $flag): \BackedEnum
    {
        if ($raw === null) {
            return $default;
        }

        $resolved = $enum::tryFrom((string) $raw);
        if ($resolved === null) {
            throw new RuntimeException("Invalid value for $flag: $raw");
        }

        return $resolved;
    }

    /**
     * @param  list<string>|null  $raw
     * @return list<Extra>
     */
    private function parseExtras(?array $raw): array
    {
        if (empty($raw)) {
            return [];
        }

        return array_values(array_map(
            static fn (string $value): Extra => Extra::tryFrom($value)
                ?? throw new RuntimeException("Unknown extra: $value"),
            $raw,
        ));
    }
}
