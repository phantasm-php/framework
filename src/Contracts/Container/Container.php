<?php

namespace WeStacks\Framework\Contracts\Container;

use Psr\Container\ContainerInterface;

interface Container extends ContainerInterface
{
    public function bind(string $abstract, callable|string|object|null $concrete = null, bool $cache = false, array $aliases = []): void;

    public function flush(): void;
}
