<?php

namespace Phantasm\Console;

use Phantasm\Foundation\Attributes\Singleton;
use Phantasm\Contracts\Console\IO as IOContract;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

#[Singleton(IOContract::class)]
class IO implements IOContract
{
    public const SUCCESS = Command::SUCCESS;
    public const FAILURE = Command::FAILURE;
    public const INVALID = Command::INVALID;

    public function __construct(
        public readonly InputInterface $input = new ArgvInput(),
        public readonly OutputInterface $output = new ConsoleOutput(),
    ) {}

    public function write(string $message): void
    {
        $this->output->writeln($message);
    }

    public function info(string $message): void
    {
        $this->output->writeln("<info>{$message}</info>");
    }

    public function error(string $message): void
    {
        $this->output->writeln("<error>{$message}</error>");
    }

    public function warning(string $message): void
    {
        $this->output->writeln("<comment>{$message}</comment>");
    }
}
