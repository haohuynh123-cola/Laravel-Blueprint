<?php

declare(strict_types=1);

namespace LaravelBlueprint\Generators;

use LaravelBlueprint\Config\BlueprintConfig;
use LaravelBlueprint\Config\Database;
use RuntimeException;

/**
 * Rewrites .env to use the chosen database driver, and (for SQLite) creates
 * the database file so `php artisan migrate` works without further setup.
 */
final class DatabaseConfigurator implements Generator
{
    public function generate(BlueprintConfig $config): void
    {
        $envPath = $config->targetPath.'/.env';
        if (! is_file($envPath)) {
            throw new RuntimeException(".env not found at $envPath — base install may have failed");
        }

        $contents = (string) file_get_contents($envPath);
        $contents = $this->applyDatabaseConfig($contents, $config);

        if (file_put_contents($envPath, $contents) === false) {
            throw new RuntimeException("Failed to write $envPath");
        }

        if ($config->database === Database::SQLite) {
            $this->ensureSqliteFile($config);
        }
    }

    private function applyDatabaseConfig(string $env, BlueprintConfig $config): string
    {
        $db = $config->database;

        $replacements = match ($db) {
            Database::SQLite => [
                'DB_CONNECTION' => 'sqlite',
                'DB_DATABASE' => 'database/database.sqlite',
            ],
            Database::MySQL, Database::MariaDB, Database::PostgreSQL => [
                'DB_CONNECTION' => $db->value,
                'DB_HOST' => '127.0.0.1',
                'DB_PORT' => (string) $db->defaultPort(),
                'DB_DATABASE' => str_replace('-', '_', $config->projectName),
                'DB_USERNAME' => 'root',
                'DB_PASSWORD' => '',
            ],
        };

        foreach ($replacements as $key => $value) {
            $env = $this->setEnvKey($env, $key, $value);
        }

        return $env;
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

    private function ensureSqliteFile(BlueprintConfig $config): void
    {
        $path = $config->targetPath.'/database/database.sqlite';
        if (is_file($path)) {
            return;
        }

        if (! is_dir(dirname($path)) && ! mkdir(dirname($path), 0o755, true) && ! is_dir(dirname($path))) {
            throw new RuntimeException('Failed to create database directory');
        }

        if (file_put_contents($path, '') === false) {
            throw new RuntimeException("Failed to create SQLite file at $path");
        }
    }
}
