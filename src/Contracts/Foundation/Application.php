<?php

namespace Phantasm\Contracts\Foundation;

use Psr\Container\ContainerInterface;

interface Application extends ContainerInterface
{
    public function version(): null|string;

    public function bind(
        BindingType $type,
        string $abstract,
        callable|string|object|null $concrete = null,
        array $aliases = [],
    ): void;

    public function flush(): void;

    public function run(...$args): mixed;
}
