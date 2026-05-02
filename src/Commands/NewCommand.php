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
use LaravelBlueprint\Generators\BaseInstaller;
use LaravelBlueprint\Generators\DatabaseConfigurator;
use LaravelBlueprint\Generators\GitInitializer;
use LaravelBlueprint\Support\ProcessRunner;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

#[AsCommand(name: 'new', description: 'Scaffold a new Laravel project')]
final class NewCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::OPTIONAL, 'Project directory name')
            ->addOption('kit', null, InputOption::VALUE_REQUIRED, 'Starter kit: ' . $this->enumValues(StarterKit::cases()))
            ->addOption('stack', null, InputOption::VALUE_REQUIRED, 'Frontend stack: ' . $this->enumValues(FrontendStack::cases()))
            ->addOption('database', null, InputOption::VALUE_REQUIRED, 'Database: ' . $this->enumValues(Database::cases()))
            ->addOption('tests', null, InputOption::VALUE_REQUIRED, 'Test runner: ' . $this->enumValues(TestRunner::cases()))
            ->addOption('extra', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Repeatable extras')
            ->addOption('docker', null, InputOption::VALUE_REQUIRED, 'Docker mode: ' . $this->enumValues(DockerMode::cases()))
            ->addOption('ci', null, InputOption::VALUE_REQUIRED, 'CI preset: ' . $this->enumValues(CiPreset::cases()))
            ->addOption('git', null, InputOption::VALUE_REQUIRED, 'Git mode: ' . $this->enumValues(GitMode::cases()))
            ->addOption('yes', 'y', InputOption::VALUE_NONE, 'Skip prompts; require all flags');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $config = $this->collectConfig($input);
            $this->runGenerators($config, $output);
            $this->printNextSteps($config);

            return Command::SUCCESS;
        } catch (RuntimeException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            return Command::FAILURE;
        }
    }

    private function collectConfig(InputInterface $input): BlueprintConfig
    {
        $nonInteractive = (bool) $input->getOption('yes');

        intro('Laravel Blueprint — scaffold a new Laravel project');

        $name = (string) ($input->getArgument('name') ?? (
            $nonInteractive
            ? throw new RuntimeException('--yes requires a project name argument')
            : text(label: 'Project name', placeholder: 'my-app', required: true, validate: $this->validateProjectName(...))
        ));

        if (($error = $this->validateProjectName($name)) !== null) {
            throw new RuntimeException($error);
        }

        $cwd = getcwd() ?: '.';
        $targetPath = rtrim($cwd, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name;

        if (is_dir($targetPath)) {
            throw new RuntimeException("Directory already exists: $targetPath");
        }

        $kit = $this->resolveEnum(
            StarterKit::class,
            $input->getOption('kit'),
            $nonInteractive,
            fn() => select('Starter kit', $this->labelsFor(StarterKit::cases()), default: StarterKit::None->value),
            StarterKit::None,
        );

        $stack = $kit === StarterKit::None
            ? FrontendStack::None
            : $this->resolveEnum(
                FrontendStack::class,
                $input->getOption('stack'),
                $nonInteractive,
                fn() => select('Frontend stack', $this->labelsFor($this->stackOptionsFor($kit)), default: FrontendStack::Blade->value),
                FrontendStack::Blade,
            );

        $database = $this->resolveEnum(
            Database::class,
            $input->getOption('database'),
            $nonInteractive,
            fn() => select('Database', $this->labelsFor(Database::cases()), default: Database::SQLite->value),
            Database::SQLite,
        );

        $tests = $this->resolveEnum(
            TestRunner::class,
            $input->getOption('tests'),
            $nonInteractive,
            fn() => select('Test runner', $this->labelsFor(TestRunner::cases()), default: TestRunner::Pest->value),
            TestRunner::Pest,
        );

        $extras = $this->resolveExtras($input->getOption('extra'), $nonInteractive);

        $docker = $this->resolveEnum(
            DockerMode::class,
            $input->getOption('docker'),
            $nonInteractive,
            fn() => select('Docker', $this->labelsFor(DockerMode::cases()), default: DockerMode::None->value),
            DockerMode::None,
        );

        $ci = $this->resolveEnum(
            CiPreset::class,
            $input->getOption('ci'),
            $nonInteractive,
            fn() => select('Continuous integration', $this->labelsFor(CiPreset::cases()), default: CiPreset::None->value),
            CiPreset::None,
        );

        $git = $this->resolveEnum(
            GitMode::class,
            $input->getOption('git'),
            $nonInteractive,
            fn() => select('Initialize git', $this->labelsFor(GitMode::cases()), default: GitMode::Commit->value),
            GitMode::Commit,
        );

        return new BlueprintConfig(
            projectName: $name,
            targetPath: $targetPath,
            starterKit: $kit,
            frontendStack: $stack,
            database: $database,
            testRunner: $tests,
            extras: $extras,
            dockerMode: $docker,
            ciPreset: $ci,
            gitMode: $git,
        );
    }

    private function runGenerators(BlueprintConfig $config, OutputInterface $output): void
    {
        $runner = new ProcessRunner($output);

        info('Installing base Laravel project…');
        (new BaseInstaller($runner))->generate($config);

        info('Configuring database…');
        (new DatabaseConfigurator())->generate($config);

        // Starter kit / extras / docker / CI generators land in subsequent versions.
        // Print what was skipped so the user knows the gap is intentional.
        $this->printPendingGenerators($config);

        if ($config->gitMode !== GitMode::Skip) {
            info('Initializing git repository…');
            (new GitInitializer($runner))->generate($config);
        }
    }

    /**
     * @param  list<Extra>|null  $extraFlags
     * @return list<Extra>
     */
    private function resolveExtras(?array $extraFlags, bool $nonInteractive): array
    {
        if (! empty($extraFlags)) {
            return array_values(array_map(
                fn(string $value): Extra => Extra::tryFrom($value)
                    ?? throw new RuntimeException("Unknown extra: $value"),
                $extraFlags,
            ));
        }

        if ($nonInteractive) {
            return [];
        }

        $selected = multiselect(
            label: 'Extras (space to toggle, enter to confirm)',
            options: $this->labelsFor(Extra::cases()),
            default: [Extra::Pint->value, Extra::Larastan->value],
        );

        return array_values(array_map(
            fn(string $value): Extra => Extra::from($value),
            $selected,
        ));
    }

    /**
     * @template T of \BackedEnum
     *
     * @param  class-string<T>  $enum
     * @param  T  $default
     * @return T
     */
    private function resolveEnum(string $enum, mixed $flagValue, bool $nonInteractive, callable $prompt, \BackedEnum $default): \BackedEnum
    {
        if ($flagValue !== null) {
            $resolved = $enum::tryFrom((string) $flagValue);
            if ($resolved === null) {
                throw new RuntimeException("Invalid value for --{$enum}: $flagValue");
            }

            return $resolved;
        }

        if ($nonInteractive) {
            return $default;
        }

        $value = $prompt();

        return $enum::from((string) $value);
    }

    /**
     * @param  list<\BackedEnum>  $cases
     * @return array<string, string>
     */
    private function labelsFor(array $cases): array
    {
        $out = [];
        foreach ($cases as $case) {
            /** @phpstan-ignore-next-line method.notFound */
            $out[$case->value] = method_exists($case, 'label') ? $case->label() : $case->name;
        }

        return $out;
    }

    /**
     * @param  list<\BackedEnum>  $cases
     */
    private function enumValues(array $cases): string
    {
        return implode('|', array_map(static fn(\BackedEnum $c) => $c->value, $cases));
    }

    /**
     * @return list<FrontendStack>
     */
    private function stackOptionsFor(StarterKit $kit): array
    {
        return match ($kit) {
            StarterKit::Breeze => [FrontendStack::Blade, FrontendStack::Livewire, FrontendStack::InertiaVue, FrontendStack::InertiaReact, FrontendStack::Api],
            StarterKit::Jetstream => [FrontendStack::Livewire, FrontendStack::InertiaVue],
            StarterKit::Filament => [FrontendStack::Blade],
            StarterKit::None => [FrontendStack::None],
        };
    }

    private function validateProjectName(string $name): ?string
    {
        if (! preg_match('/^[a-z0-9][a-z0-9\-_]*$/i', $name)) {
            return 'Use letters, digits, dashes, or underscores. Must start alphanumeric.';
        }

        return null;
    }

    private function printPendingGenerators(BlueprintConfig $config): void
    {
        $pending = [];
        if ($config->starterKit !== StarterKit::None) {
            $pending[] = "starter kit ({$config->starterKit->value})";
        }
        if ($config->extras !== []) {
            $pending[] = 'extras: ' . implode(', ', array_map(fn(Extra $e) => $e->value, $config->extras));
        }
        if ($config->dockerMode !== DockerMode::None) {
            $pending[] = "docker ({$config->dockerMode->value})";
        }
        if ($config->ciPreset !== CiPreset::None) {
            $pending[] = "ci ({$config->ciPreset->value})";
        }

        if ($pending !== []) {
            note('Recorded but not yet applied (coming in next release): ' . implode('; ', $pending));
        }
    }

    private function printNextSteps(BlueprintConfig $config): void
    {
        outro("Created {$config->projectName} at {$config->targetPath}");

        $output = "  cd {$config->projectName}\n";
        $output .= "  php artisan serve\n";
        info("Next steps:\n" . $output);
    }
}
