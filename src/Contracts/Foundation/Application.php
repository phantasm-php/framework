<?php

namespace WeStacks\Framework\Contracts\Foundation;

use Psr\Container\ContainerInterface;
use WeStacks\Framework\Contracts\Discovery\Discover;

interface Application extends ContainerInterface
{
    public function bind(BindingType $type, string $abstract, callable|string|object|null $concrete = null, array $aliases = []): void;

    public function flush(): void;

    public function run(): void;
}
