<?php

namespace WeStacks\Framework\Foundation\Attributes;

use WeStacks\Framework\Contracts\Foundation\Application;
use WeStacks\Framework\Foundation\Discovery\Discoverable;
use WeStacks\Framework\Foundation\Discovery\Installable;

#[\Attribute]
class Bind extends Discoverable implements Installable
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

    public function install(Application $app): void
    {
        $app->bind($this->source, $this->source, false, $this->aliases);
    }
}
