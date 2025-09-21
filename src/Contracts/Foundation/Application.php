<?php

namespace WeStacks\Framework\Contracts\Foundation;

use Psr\Container\ContainerInterface;
use WeStacks\Framework\Contracts\Discovery\Discover;

interface Application extends ContainerInterface
{
    public function bind(string $abstract, callable|string|object|null $concrete = null, bool $cache = false, array $aliases = []): void;

    public function flush(): void;

    public function run(): void;
}
