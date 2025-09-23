<?php

namespace WeStacks\Framework\Console;

use Symfony\Component\Console\Application as Console;
use WeStacks\Framework\Contracts\Console\Kernel as KernelContract;
use WeStacks\Framework\Contracts\Foundation\Application;
use WeStacks\Framework\Foundation\Attributes\Singleton;

#[Singleton(KernelContract::class)]
class Kernel extends Console implements KernelContract
{
    public function __construct(
        protected Application $app,
        string $name = 'UNKNOWN',
        string $version = 'UNKNOWN',
    ) {
        parent::__construct($name, $version);
    }
}
