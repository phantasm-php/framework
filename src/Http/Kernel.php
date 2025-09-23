<?php

namespace WeStacks\Framework\Http;

use WeStacks\Framework\Contracts\Foundation\Application;
use WeStacks\Framework\Contracts\Http\Kernel as KernelContract;
use WeStacks\Framework\Foundation\Attributes\Singleton;

#[Singleton(KernelContract::class)]
class Kernel implements KernelContract
{
    public function __construct(
        protected Application $app,
    ) {}

    public function run(...$args)
    {
        echo 'WEB';

        return;
    }
}
