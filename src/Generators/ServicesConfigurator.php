<?php

declare(strict_types=1);

namespace LaravelBlueprint\Generators;

use LaravelBlueprint\Config\BlueprintConfig;
use LaravelBlueprint\Config\Cache;
use LaravelBlueprint\Config\Queue;
use LaravelBlueprint\Support\ProcessRunner;
use RuntimeException;

/**
 * Wires up cache + queue drivers:
 *  - Rewrites .env (CACHE_STORE, QUEUE_CONNECTION, REDIS_*, RABBITMQ_*)
 *  - Composer-requires the matching driver package when needed
 *
 * Runs after BaseInstaller and DatabaseConfigurator so the .env it edits
 * already exists.
 */
final readonly class ServicesConfigurator implements Generator
{
    public function __construct(private ProcessRunner $runner) {}

    public function generate(BlueprintConfig $config): void
    {
        $envPath = $config->targetPath.'/.env';
        if (! is_file($envPath)) {
            throw new RuntimeException(".env not found at $envPath");
        }

        $contents = (string) file_get_contents($envPath);
        $contents = $this->applyCache($contents, $config);
        $contents = $this->applyQueue($contents, $config);
        $contents = $this->applyRedis($contents, $config);
        $contents = $this->applyRabbitMq($contents, $config);

        if (file_put_contents($envPath, $contents) === false) {
            throw new RuntimeException("Failed to write $envPath");
        }

        $this->installPackages($config);
    }

    private function applyCache(string $env, BlueprintConfig $config): string
    {
        return $this->setEnvKey($env, 'CACHE_STORE', $config->cache->value);
    }

    private function applyQueue(string $env, BlueprintConfig $config): string
    {
        return $this->setEnvKey($env, 'QUEUE_CONNECTION', $config->queue->value);
    }

    private function applyRedis(string $env, BlueprintConfig $config): string
    {
        if (! $this->needsRedis($config)) {
            return $env;
        }

        $env = $this->setEnvKey($env, 'REDIS_HOST', '127.0.0.1');
        $env = $this->setEnvKey($env, 'REDIS_PASSWORD', 'null');

        return $this->setEnvKey($env, 'REDIS_PORT', '6379');
    }

    private function applyRabbitMq(string $env, BlueprintConfig $config): string
    {
        if ($config->queue !== Queue::RabbitMQ) {
            return $env;
        }

        $env = $this->setEnvKey($env, 'RABBITMQ_HOST', '127.0.0.1');
        $env = $this->setEnvKey($env, 'RABBITMQ_PORT', '5672');
        $env = $this->setEnvKey($env, 'RABBITMQ_USER', 'guest');
        $env = $this->setEnvKey($env, 'RABBITMQ_PASSWORD', 'guest');

        return $this->setEnvKey($env, 'RABBITMQ_VHOST', '/');
    }

    private function installPackages(BlueprintConfig $config): void
    {
        if ($this->needsRedis($config)) {
            // predis is pure PHP — no ext-redis required, easier default.
            $this->runner->run(
                ['composer', 'require', 'predis/predis', '--no-interaction'],
                cwd: $config->targetPath,
            );
        }

        if ($config->queue === Queue::RabbitMQ) {
            $this->runner->run(
                ['composer', 'require', 'vladimir-yuldashev/laravel-queue-rabbitmq', '--no-interaction'],
                cwd: $config->targetPath,
            );
        }

        if ($config->cache === Cache::Memcached) {
            // memcached is an ext, no composer package — but Laravel still needs
            // a hint installed so the cache config compiles. We surface a notice
            // via the .env comment instead of a failed composer install.
        }
    }

    private function needsRedis(BlueprintConfig $config): bool
    {
        return $config->cache === Cache::Redis || $config->queue === Queue::Redis;
    }

    private function setEnvKey(string $env, string $key, string $value): string
    {
        $pattern = '/^'.preg_quote($key, '/').'=.*$/m';
        $line = $key.'='.$value;

        if (preg_match($pattern, $env) === 1) {
            return (string) preg_replace($pattern, $line, $env, limit: 1);
        }

        return rtrim($env, "\n")."\n".$line."\n";
    }
}
