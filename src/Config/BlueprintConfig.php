<?php

declare(strict_types=1);

namespace LaravelBlueprint\Config;

/**
 * Immutable record of every choice the user made for a new project.
 *
 * Construct once at the end of the prompts/flag phase, then pass through
 * every generator. Generators must never mutate it.
 */
final readonly class BlueprintConfig
{
    /**
     * @param  list<Extra>  $extras
     */
    public function __construct(
        public string $projectName,
        public string $targetPath,
        public StarterKit $starterKit,
        public FrontendStack $frontendStack,
        public Database $database,
        public Cache $cache,
        public Queue $queue,
        public TestRunner $testRunner,
        public array $extras,
        public DockerMode $dockerMode,
        public CiPreset $ciPreset,
        public GitMode $gitMode,
    ) {}

    public function hasExtra(Extra $extra): bool
    {
        return in_array($extra, $this->extras, strict: true);
    }
}
