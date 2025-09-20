<?php

namespace Framework\Core;

#[\Attribute]
class Singleton extends Discoverable
{
    public function __construct(
        protected ?string $key = null,
        protected array $aliases = [],
    ) {}

    public function setSource(\Reflector $source): void
    {
        if (! $source instanceof \ReflectionClass) {
            throw new \Exception("You can only bind classes");
        }

        if ($source->isAnonymous()) {
            $this->source = $source->getFileName();
            $this->key ??= $source->getFileName();
        } else {
            $this->source = $source->getName();
            $this->key ??= $source->getName();
        }
    }
}
