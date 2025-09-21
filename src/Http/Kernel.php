<?php

namespace WeStacks\Framework\Http;

use WeStacks\Framework\Contracts\Foundation\Application;
use WeStacks\Framework\Foundation\Attributes\Singleton;
use WeStacks\Framework\Contracts\Http\Kernel as KernelContract;

#[Singleton(KernelContract::class)]
class Kernel implements KernelContract
{
    public function __construct(
        protected Application $app,
    ) {}

    public function handle(): void
    {
        echo 'WEB';
    }
}
