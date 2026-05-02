<?php

declare(strict_types=1);

namespace LaravelBlueprint\Generators;

use LaravelBlueprint\Config\BlueprintConfig;
use LaravelBlueprint\Config\DockerMode;
use LaravelBlueprint\Support\ProcessRunner;
use LaravelBlueprint\Support\StubLoader;

/**
 * Materializes the chosen Docker setup.
 *
 * - `production` / `both` → writes a Dockerfile, nginx + php.ini, .dockerignore
 * - `sail` / `both`       → installs laravel/sail and runs sail:install
 * - `none`                → no-op
 */
final readonly class DockerGenerator implements Generator
{
    public function __construct(
        private ProcessRunner $runner,
        private StubLoader $stubs = new StubLoader(),
    ) {}

    public function generate(BlueprintConfig $config): void
    {
        if ($config->dockerMode === DockerMode::None) {
            return;
        }

        if (in_array($config->dockerMode, [DockerMode::Production, DockerMode::Both], true)) {
            $this->writeProductionFiles($config);
        }

        if (in_array($config->dockerMode, [DockerMode::Sail, DockerMode::Both], true)) {
            $this->installSail($config);
        }
    }

    private function writeProductionFiles(BlueprintConfig $config): void
    {
        $vars = ['project_name' => $config->projectName];
        $base = $config->targetPath;

        $this->stubs->write('docker/Dockerfile.stub', $base.'/Dockerfile', $vars);
        $this->stubs->write('docker/nginx.conf.stub', $base.'/docker/nginx.conf', $vars);
        $this->stubs->write('docker/php.ini.stub', $base.'/docker/php.ini', $vars);
        $this->stubs->write('docker/dockerignore.stub', $base.'/.dockerignore', $vars);
    }

    private function installSail(BlueprintConfig $config): void
    {
        $this->runner->run(
            ['composer', 'require', 'laravel/sail', '--dev', '--no-interaction'],
            cwd: $config->targetPath,
        );

        $services = $this->sailServices($config);

        $this->runner->run(
            ['php', 'artisan', 'sail:install', '--with='.implode(',', $services), '--no-interaction'],
            cwd: $config->targetPath,
        );
    }

    /**
     * @return list<string>
     */
    private function sailServices(BlueprintConfig $config): array
    {
        // sail:install accepts: mysql, pgsql, mariadb, redis, memcached,
        // meilisearch, typesense, minio, mailpit, selenium, soketi.
        $services = [$config->database->value];

        if ($config->cache->value === 'redis' || $config->queue->value === 'redis') {
            $services[] = 'redis';
        }
        if ($config->cache->value === 'memcached') {
            $services[] = 'memcached';
        }

        // De-duplicate while preserving order.
        return array_values(array_unique($services));
    }
}
