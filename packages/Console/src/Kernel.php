<?php

namespace Phantasm\Console;

use Phantasm\Console\IO\Input;
use Phantasm\Console\IO\Output;
use Phantasm\Container\Bindings\Singleton;
use Phantasm\Contracts\Console\IO\Input as InputContract;
use Phantasm\Contracts\Console\IO\Output as OutputContract;
use Phantasm\Contracts\Console\Kernel as KernelContract;

#[Singleton(KernelContract::class)]
class Kernel implements KernelContract
{
    protected array $commands = [];

    public function handle(?InputContract $input = null, ?OutputContract $output = null): int
    {
        $input ??= new Input();
        $output ??= new Output();

        if (null === $command = $input->argument(0)) {
            $output->info('Phantasm Console');

            return 0;
        }

        if (null === $command = $this->commands[$command] ?? null) {
            throw new \InvalidArgumentException('Command not found.');
        };

        return 0;
    }
}
