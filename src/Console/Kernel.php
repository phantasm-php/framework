<?php

namespace Phantasm\Console;

use Symfony\Component\Console\Application as Console;
use Phantasm\Contracts\Console\Kernel as KernelContract;
use Phantasm\Contracts\Foundation\Application;
use Phantasm\Foundation\Attributes\Singleton;

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
