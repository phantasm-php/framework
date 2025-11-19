<?php

namespace Phantasm\Container\Bindings;

use Phantasm\Container\Binding;
use Phantasm\Contracts\Container\Container;
use Phantasm\Contracts\Foundation\Extension;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Scoped implements Extension
{
    public readonly array $aliases;

    public function __construct(string ...$aliases)
    {
        $this->aliases = $aliases ?? [];
    }

    /**
     * {@inheritDoc}
     * @param \ReflectionClass $reflection
     */
    #[\Override]
    public static function install(Container $container, \Reflector $reflection, null|Extension $context): null|callable
    {
        return $container->set(Binding::SCOPED, $reflection->getName(), null, $context?->aliases);
    }
}
