<?php

namespace Phantasm\Container\Bindings;

use Phantasm\Contracts\Container\Container;
use Phantasm\Contracts\Foundation\Provider;
use Phantasm\Container\Binding;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Scoped implements Provider
{
    public readonly array $aliases;

    public function __construct(string ...$aliases)
    {
        $this->aliases = $aliases ?? [];
    }

    /**
     * @param \ReflectionClass $reflection
     * @param static $context
     */
    public static function register(Container $container, \Reflector $reflection, ?Provider $context): void
    {
        $container->set(Binding::SCOPED, $reflection->getName(), null, $context->aliases);
    }

    public static function boot(Container $container, \Reflector $reflection, ?Provider $context): void
    {
        //
    }
}
