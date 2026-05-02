<?php

declare(strict_types=1);

use LaravelBlueprint\Support\StubLoader;

beforeEach(function (): void {
    $this->stubRoot = sys_get_temp_dir().'/blueprint-stubs-'.bin2hex(random_bytes(4));
    mkdir($this->stubRoot.'/group', 0o755, true);

    file_put_contents(
        $this->stubRoot.'/group/sample.stub',
        "Hello {{ name }}!\nVersion: {{ version }}\n",
    );
});

afterEach(function (): void {
    if (! is_dir($this->stubRoot)) {
        return;
    }
    $iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($this->stubRoot, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );
    foreach ($iter as $file) {
        $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
    }
    rmdir($this->stubRoot);
});

it('substitutes placeholders', function (): void {
    $loader = new StubLoader($this->stubRoot);

    $output = $loader->render('group/sample.stub', ['name' => 'Sunny', 'version' => '0.2.0']);

    expect($output)->toBe("Hello Sunny!\nVersion: 0.2.0\n");
});

it('throws when a placeholder is left unsubstituted', function (): void {
    $loader = new StubLoader($this->stubRoot);

    $loader->render('group/sample.stub', ['name' => 'Sunny']);
})->throws(RuntimeException::class, 'Unsubstituted placeholder');

it('throws when the stub does not exist', function (): void {
    $loader = new StubLoader($this->stubRoot);

    $loader->render('nope.stub', []);
})->throws(RuntimeException::class, 'Stub not found');

it('does not match GitHub Actions ${{ }} expressions', function (): void {
    file_put_contents(
        $this->stubRoot.'/group/actions.stub',
        "key: composer-\${{ hashFiles('composer.lock') }}\nname: {{ project_name }}\n",
    );

    $loader = new StubLoader($this->stubRoot);
    $output = $loader->render('group/actions.stub', ['project_name' => 'demo']);

    expect($output)->toContain("\${{ hashFiles('composer.lock') }}")
        ->and($output)->toContain('name: demo');
});

it('writes rendered output to disk and creates intermediate directories', function (): void {
    $loader = new StubLoader($this->stubRoot);
    $target = $this->stubRoot.'/out/nested/result.txt';

    $loader->write('group/sample.stub', $target, ['name' => 'X', 'version' => '1']);

    expect(file_exists($target))->toBeTrue()
        ->and((string) file_get_contents($target))->toBe("Hello X!\nVersion: 1\n");
});
