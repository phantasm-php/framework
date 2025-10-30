<?php

namespace Phantasm\Container\Bindings;

use Phantasm\Contracts\Container\Container;
use Phantasm\Contracts\Foundation\Provider;
use Phantasm\Container\Binding;

#[\Attribute]
class Bind implements Provider
{
    public readonly array $aliases;

    public function __construct(string ...$aliases)
    {
        $this->aliases = $aliases ?? [];
    }

    /** @param static $context */
    public static function register(Container $container, \Reflector $reflection, ?Provider $context): void
    {
        if (! $context) {
            return;
        }

        if (! $reflection instanceof \ReflectionClass) {
            throw new \Exception('Only classes can be bound into the container.');
        }

        $container->set($reflection->getName(), null, Binding::RESOLVE, $context->aliases);
    }

    public static function boot(Container $container, \Reflector $reflection, ?Provider $context): void
    {
        //
    }
}
