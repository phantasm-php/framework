<?php

namespace Phantasm\Http;

use Phantasm\Contracts\Foundation\Application;
use Phantasm\Contracts\Http\Kernel as KernelContract;
use Phantasm\Foundation\Attributes\Singleton;

#[Singleton(KernelContract::class)]
class Kernel implements KernelContract
{
    public function __construct(
        protected Application $app,
    ) {}

    public function run(...$args)
    {
        echo 'WEB';
    }
}
