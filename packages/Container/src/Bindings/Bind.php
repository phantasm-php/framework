<?php

namespace Phantasm\Container\Bindings;

use Phantasm\Container\Binding;
use Phantasm\Contracts\Container\Container;
use Phantasm\Contracts\Foundation\Extension;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Bind implements Extension
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
    #[\Override]
    public static function install(Container $container, \Reflector $reflection, null|Extension $context): null|callable
    {
        return $container->set(Binding::RESOLVE, $reflection->getName(), null, $context->aliases);
    }
}
