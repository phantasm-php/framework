<?php

namespace Phantasm\Console;

use Phantasm\Contracts\Console\IO;
use Phantasm\Contracts\Console\Kernel as KernelContract;
use Phantasm\Contracts\Foundation\Application;
use Phantasm\Foundation\Attributes\Singleton;
use Symfony\Component\Console\Application as Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function Phantasm\Foundation\env;

#[Singleton(KernelContract::class)]
class Kernel extends Console implements KernelContract
{
    public function __construct(
        protected Application $app,
    ) {
        parent::__construct(env('APP_NAME', 'Phantasm'), $app->version() ?? 'UNKNOWN');
    }

    public function run(null|InputInterface $input = null, null|OutputInterface $output = null): int
    {
        if (!$input || !$output) {
            $io = $this->app->get(IO::class);
            $input ??= $io->input;
            $output ??= $io->output;
        }

        return parent::run($input, $output);
    }
}
