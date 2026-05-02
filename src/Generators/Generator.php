<?php

declare(strict_types=1);

namespace LaravelBlueprint\Generators;

use LaravelBlueprint\Config\BlueprintConfig;

interface Generator
{
    public function generate(BlueprintConfig $config): void;
}
