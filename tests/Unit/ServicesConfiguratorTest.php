<?php

declare(strict_types=1);

use LaravelBlueprint\Config\BlueprintConfig;
use LaravelBlueprint\Config\Cache;
use LaravelBlueprint\Config\CiPreset;
use LaravelBlueprint\Config\Database;
use LaravelBlueprint\Config\DockerMode;
use LaravelBlueprint\Config\FrontendStack;
use LaravelBlueprint\Config\GitMode;
use LaravelBlueprint\Config\Queue;
use LaravelBlueprint\Config\StarterKit;
use LaravelBlueprint\Config\TestRunner;
use LaravelBlueprint\Generators\ServicesConfigurator;
use LaravelBlueprint\Support\ProcessRunner;
use Symfony\Component\Console\Output\NullOutput;

beforeEach(function (): void {
    $this->workspace = sys_get_temp_dir().'/blueprint-svc-'.bin2hex(random_bytes(4));
    mkdir($this->workspace, 0o755, true);
    file_put_contents($this->workspace.'/.env', <<<'ENV'
        APP_NAME=Laravel
        CACHE_STORE=database
        QUEUE_CONNECTION=database
        ENV);
});

afterEach(function (): void {
    if (! is_dir($this->workspace)) {
        return;
    }
    $iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($this->workspace, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );
    foreach ($iter as $file) {
        $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
    }
    rmdir($this->workspace);
});

/**
 * Stub runner that records commands instead of executing them — keeps the
 * test isolated from the network and from composer.
 */
function makeRecordingRunner(array &$commands): ProcessRunner
{
    return new class($commands) extends ProcessRunner {
        /** @param array<int, list<string>> $sink */
        public function __construct(private array &$sink)
        {
            parent::__construct(new NullOutput());
        }

        public function run(array $command, ?string $cwd = null, int $timeout = 600): void
        {
            $this->sink[] = $command;
        }
    };
}

function makeServicesConfig(string $workspace, Cache $cache, Queue $queue): BlueprintConfig
{
    return new BlueprintConfig(
        projectName: 'demo',
        targetPath: $workspace,
        starterKit: StarterKit::None,
        frontendStack: FrontendStack::None,
        database: Database::SQLite,
        cache: $cache,
        queue: $queue,
        testRunner: TestRunner::Pest,
        extras: [],
        dockerMode: DockerMode::None,
        ciPreset: CiPreset::None,
        gitMode: GitMode::Skip,
    );
}

it('rewrites .env for the chosen cache + queue drivers', function (): void {
    $commands = [];
    $runner = makeRecordingRunner($commands);

    (new ServicesConfigurator($runner))->generate(
        makeServicesConfig($this->workspace, Cache::Redis, Queue::Redis),
    );

    $env = (string) file_get_contents($this->workspace.'/.env');

    expect($env)->toContain('CACHE_STORE=redis')
        ->and($env)->toContain('QUEUE_CONNECTION=redis')
        ->and($env)->toContain('REDIS_HOST=127.0.0.1')
        ->and($env)->toContain('REDIS_PORT=6379');
});

it('installs predis exactly once when redis is used for cache or queue', function (): void {
    $commands = [];
    $runner = makeRecordingRunner($commands);

    (new ServicesConfigurator($runner))->generate(
        makeServicesConfig($this->workspace, Cache::Redis, Queue::Redis),
    );

    $predisCalls = array_filter(
        $commands,
        static fn (array $cmd): bool => in_array('predis/predis', $cmd, strict: true),
    );

    expect($predisCalls)->toHaveCount(1);
});

it('installs the rabbitmq queue package when queue is rabbitmq', function (): void {
    $commands = [];
    $runner = makeRecordingRunner($commands);

    (new ServicesConfigurator($runner))->generate(
        makeServicesConfig($this->workspace, Cache::Database, Queue::RabbitMQ),
    );

    $env = (string) file_get_contents($this->workspace.'/.env');

    expect($env)->toContain('QUEUE_CONNECTION=rabbitmq')
        ->and($env)->toContain('RABBITMQ_HOST=127.0.0.1');

    $rabbitCalls = array_filter(
        $commands,
        static fn (array $cmd): bool => in_array('vladimir-yuldashev/laravel-queue-rabbitmq', $cmd, strict: true),
    );

    expect($rabbitCalls)->toHaveCount(1);
});

it('does not install redis or rabbitmq when both drivers are database', function (): void {
    $commands = [];
    $runner = makeRecordingRunner($commands);

    (new ServicesConfigurator($runner))->generate(
        makeServicesConfig($this->workspace, Cache::Database, Queue::Database),
    );

    $env = (string) file_get_contents($this->workspace.'/.env');

    expect($env)->not->toContain('REDIS_HOST')
        ->and($env)->not->toContain('RABBITMQ_HOST')
        ->and($commands)->toBe([]);
});
