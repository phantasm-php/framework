<?php

namespace Phantasm\Container\Bindings;

use Phantasm\Contracts\Container\Container;
use Phantasm\Contracts\Foundation\Extension;
use Phantasm\Container\Binding;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Singleton implements Extension
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
    public static function install(Container $container, \Reflector $reflection, ?Extension $context): ?callable
    {
        return $container->set(Binding::SINGLETON, $reflection->getName(), null, $context->aliases);
    }
}
