<?php

namespace Phantasm\Console;

use Phantasm\Contracts\Console\IO;
use Symfony\Component\Console\Command\Command;

use function Phantasm\Foundation\app;

trait HasConsoleIO
{
    public const SUCCESS = Command::SUCCESS;

    public const FAILURE = Command::FAILURE;

    public const INVALID = Command::INVALID;

    protected function io(): IO
    {
        return app(IO::class);
    }

    public function write(string $message): void
    {
        $this->io()->write($message);
    }

    public function info(string $message): void
    {
        $this->io()->info($message);
    }

    public function error(string $message): void
    {
        $this->io()->error($message);
    }

    public function warning(string $message): void
    {
        $this->io()->warning($message);
    }
}
