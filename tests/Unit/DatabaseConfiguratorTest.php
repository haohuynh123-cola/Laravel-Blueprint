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
use LaravelBlueprint\Generators\DatabaseConfigurator;

beforeEach(function (): void {
    $this->workspace = sys_get_temp_dir().'/blueprint-test-'.bin2hex(random_bytes(4));
    mkdir($this->workspace, 0o755, true);

    file_put_contents($this->workspace.'/.env', <<<'ENV'
        APP_NAME=Laravel
        DB_CONNECTION=sqlite
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=laravel
        DB_USERNAME=root
        DB_PASSWORD=
        ENV);
});

afterEach(function (): void {
    if (is_dir($this->workspace)) {
        $iter = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->workspace, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($iter as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->workspace);
    }
});

function makeConfig(string $workspace, Database $db): BlueprintConfig
{
    return new BlueprintConfig(
        projectName: 'demo',
        targetPath: $workspace,
        starterKit: StarterKit::None,
        frontendStack: FrontendStack::None,
        database: $db,
        cache: Cache::Database,
        queue: Queue::Database,
        testRunner: TestRunner::Pest,
        extras: [],
        dockerMode: DockerMode::None,
        ciPreset: CiPreset::None,
        gitMode: GitMode::Skip,
    );
}

it('rewrites .env for postgres and sets standard fields', function (): void {
    (new DatabaseConfigurator())->generate(makeConfig($this->workspace, Database::PostgreSQL));

    $env = (string) file_get_contents($this->workspace.'/.env');

    expect($env)->toContain('DB_CONNECTION=pgsql')
        ->and($env)->toContain('DB_PORT=5432')
        ->and($env)->toContain('DB_DATABASE=demo');
});

it('creates the SQLite file when SQLite is chosen', function (): void {
    mkdir($this->workspace.'/database');
    (new DatabaseConfigurator())->generate(makeConfig($this->workspace, Database::SQLite));

    $env = (string) file_get_contents($this->workspace.'/.env');

    expect($env)->toContain('DB_CONNECTION=sqlite')
        ->and($env)->toContain('DB_DATABASE=database/database.sqlite')
        ->and(file_exists($this->workspace.'/database/database.sqlite'))->toBeTrue();
});
