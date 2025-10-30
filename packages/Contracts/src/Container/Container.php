<?php

namespace Phantasm\Contracts\Container;

use Psr\Container\ContainerInterface;

interface Container extends ContainerInterface
{
    public function set(string $id, $instance = null, Binding $binding, array $aliases = []): void;

    public function resolve(string $id): mixed;
}
