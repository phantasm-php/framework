<?php

namespace WeStacks\Framework\Console;

use WeStacks\Framework\Contracts\Console\Kernel as KernelContract;
use WeStacks\Framework\Contracts\Foundation\Application;
use WeStacks\Framework\Foundation\Attributes\Singleton;

#[Singleton(KernelContract::class)]
class Kernel implements KernelContract
{
    public function __construct(
        protected Application $app,
    ) {}

    public function handle(): void
    {
        echo 'CLI';
    }
}
