<?php

declare(strict_types=1);

namespace LaravelBlueprint\Support;

use RuntimeException;

/**
 * Loads template files from src/Templates and substitutes {{ key }} markers.
 *
 * Why a real file loader instead of inline strings (the path go-blueprint took)?
 * Stubs stay in their target language — Dockerfile syntax, YAML, .neon — so they
 * are syntax-highlighted, lintable, and editable in isolation.
 *
 * Substitution is intentionally simple: literal `{{ name }}` placeholders only.
 * No conditionals, no loops, no Twig-style escaping. If a template needs logic,
 * split it into multiple stubs and choose between them in the generator.
 */
final class StubLoader
{
    private readonly string $stubRoot;

    public function __construct(?string $stubRoot = null)
    {
        $this->stubRoot = $stubRoot ?? dirname(__DIR__).'/Templates';
    }

    /**
     * @param  array<string, string>  $vars
     */
    public function render(string $relativePath, array $vars = []): string
    {
        $absolute = $this->stubRoot.DIRECTORY_SEPARATOR.$relativePath;

        if (! is_file($absolute)) {
            throw new RuntimeException("Stub not found: $relativePath");
        }

        $contents = (string) file_get_contents($absolute);

        foreach ($vars as $key => $value) {
            $contents = str_replace('{{ '.$key.' }}', $value, $contents);
        }

        // Surface unsubstituted markers as a fail-fast — easier than hunting
        // weird-looking {{ foo }} text inside a generated file later.
        if (preg_match('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', $contents, $match) === 1) {
            throw new RuntimeException(
                "Unsubstituted placeholder {{ {$match[1]} }} in stub: $relativePath",
            );
        }

        return $contents;
    }

    /**
     * Render and write to the target project.
     *
     * @param  array<string, string>  $vars
     */
    public function write(string $relativeStubPath, string $absoluteTargetPath, array $vars = []): void
    {
        $contents = $this->render($relativeStubPath, $vars);

        $dir = dirname($absoluteTargetPath);
        if (! is_dir($dir) && ! mkdir($dir, 0o755, true) && ! is_dir($dir)) {
            throw new RuntimeException("Failed to create directory: $dir");
        }

        if (file_put_contents($absoluteTargetPath, $contents) === false) {
            throw new RuntimeException("Failed to write file: $absoluteTargetPath");
        }
    }
}
