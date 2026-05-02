<?php

declare(strict_types=1);

namespace LaravelBlueprint;

use LaravelBlueprint\Commands\NewCommand;
use Symfony\Component\Console\Application as SymfonyApplication;

final class Application extends SymfonyApplication
{
    public const VERSION = '0.0.1';

    public function __construct()
    {
        parent::__construct('Laravel Blueprint', self::VERSION);

        $this->add(new NewCommand());
        $this->setDefaultCommand('new', false);
    }
}
