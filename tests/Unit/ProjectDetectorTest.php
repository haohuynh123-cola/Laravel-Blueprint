<?php

declare(strict_types=1);

use LaravelBlueprint\Support\ProjectDetector;

beforeEach(function (): void {
    $this->workspace = sys_get_temp_dir().'/blueprint-pd-'.bin2hex(random_bytes(4));
    mkdir($this->workspace, 0o755, true);
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

function fakeLaravel(string $path, ?string $name = 'acme/widgets'): void
{
    file_put_contents($path.'/artisan', "#!/usr/bin/env php\n<?php\n");
    file_put_contents($path.'/composer.json', json_encode([
        'name' => $name,
        'require' => [
            'laravel/framework' => '^11.0',
            'php' => '^8.2',
        ],
        'require-dev' => [
            'pestphp/pest' => '^3.0',
        ],
    ]));
}

it('detects a Laravel project', function (): void {
    fakeLaravel($this->workspace);

    expect((new ProjectDetector($this->workspace))->isLaravelProject())->toBeTrue();
});

it('rejects an empty directory', function (): void {
    expect((new ProjectDetector($this->workspace))->isLaravelProject())->toBeFalse();
});

it('rejects a composer.json without laravel/framework', function (): void {
    file_put_contents($this->workspace.'/artisan', '#!/usr/bin/env php');
    file_put_contents($this->workspace.'/composer.json', json_encode(['name' => 'a/b']));

    expect((new ProjectDetector($this->workspace))->isLaravelProject())->toBeFalse();
});

it('extracts project name from composer.json', function (): void {
    fakeLaravel($this->workspace, 'acme/widgets');

    expect((new ProjectDetector($this->workspace))->projectName())->toBe('widgets');
});

it('falls back to directory name when composer.json has no name', function (): void {
    fakeLaravel($this->workspace, name: null);

    expect((new ProjectDetector($this->workspace))->projectName())
        ->toBe(basename($this->workspace));
});

it('detects required packages', function (): void {
    fakeLaravel($this->workspace);

    $detector = new ProjectDetector($this->workspace);

    expect($detector->hasComposerPackage('laravel/framework'))->toBeTrue()
        ->and($detector->hasComposerPackage('pestphp/pest'))->toBeTrue()
        ->and($detector->hasComposerPackage('vendor/missing'))->toBeFalse();
});
