<?php

namespace Phantasm\Contracts\Container;

use Psr\Container\ContainerInterface;

interface Container extends ContainerInterface
{
    public function set(Binding $binding, string $id, $instance = null, array $aliases = []): void;

    public function resolve(string $id): mixed;
}
