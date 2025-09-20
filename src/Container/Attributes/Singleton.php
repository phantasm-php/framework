<?php

namespace WeStacks\Framework\Container\Attributes;

use WeStacks\Framework\Contracts\Container\Container;
use WeStacks\Framework\Discovery\Discoverable;
use WeStacks\Framework\Discovery\Installable;

#[\Attribute]
class Singleton extends Discoverable implements Installable
{
    protected array $aliases;

    public function __construct(string ...$aliases)
    {
        $this->aliases = $aliases ?? [];
    }

    public function setSource(\Reflector $source): void
    {
        if (! $source instanceof \ReflectionClass) {
            throw new \Exception("You can only bind classes");
        }
        $this->source = $source->getName();
    }

    public function install(Container $container): void
    {
        $container->bind($this->source, $this->source, true, $this->aliases);
    }
}
