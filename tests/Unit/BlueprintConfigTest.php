<?php

declare(strict_types=1);

use LaravelBlueprint\Config\BlueprintConfig;
use LaravelBlueprint\Config\Cache;
use LaravelBlueprint\Config\CiPreset;
use LaravelBlueprint\Config\Database;
use LaravelBlueprint\Config\DockerMode;
use LaravelBlueprint\Config\Extra;
use LaravelBlueprint\Config\FrontendStack;
use LaravelBlueprint\Config\GitMode;
use LaravelBlueprint\Config\Queue;
use LaravelBlueprint\Config\StarterKit;
use LaravelBlueprint\Config\TestRunner;

it('records every choice immutably', function (): void {
    $config = new BlueprintConfig(
        projectName: 'demo',
        targetPath: '/tmp/demo',
        starterKit: StarterKit::Breeze,
        frontendStack: FrontendStack::InertiaVue,
        database: Database::PostgreSQL,
        cache: Cache::Redis,
        queue: Queue::RabbitMQ,
        testRunner: TestRunner::Pest,
        extras: [Extra::Pint, Extra::Larastan],
        dockerMode: DockerMode::Production,
        ciPreset: CiPreset::GitHubActions,
        gitMode: GitMode::Commit,
    );

    expect($config->projectName)->toBe('demo')
        ->and($config->hasExtra(Extra::Pint))->toBeTrue()
        ->and($config->hasExtra(Extra::Horizon))->toBeFalse();
});
