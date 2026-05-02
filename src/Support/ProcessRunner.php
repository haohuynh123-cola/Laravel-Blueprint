<?php

declare(strict_types=1);

namespace LaravelBlueprint\Support;

use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Thin wrapper around symfony/process so generators get one consistent
 * place for streaming output, timeouts, and failure handling.
 */
final class ProcessRunner
{
    public function __construct(private readonly OutputInterface $output) {}

    /**
     * @param  list<string>  $command
     */
    public function run(array $command, ?string $cwd = null, int $timeout = 600): void
    {
        $this->output->writeln('<comment>$ ' . implode(' ', $command) . '</comment>');

        $process = new Process($command, $cwd, timeout: $timeout);
        $process->setTty(Process::isTtySupported());

        $exitCode = $process->run(function (string $type, string $buffer): void {
            $this->output->write($buffer);
        });

        if ($exitCode !== 0) {
            throw new RuntimeException(sprintf(
                'Command failed (exit %d): %s',
                $exitCode,
                implode(' ', $command),
            ));
        }
    }
}
