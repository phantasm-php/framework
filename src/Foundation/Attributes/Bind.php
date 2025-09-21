<?php

namespace WeStacks\Framework\Foundation\Attributes;

use WeStacks\Framework\Contracts\Foundation\Application;
use WeStacks\Framework\Contracts\Foundation\Discovery\Installable;
use WeStacks\Framework\Foundation\BindingType;

#[\Attribute]
class Bind implements Installable
{
    public array $aliases;

    public function __construct(string ...$aliases)
    {
        $this->aliases = $aliases ?? [];
    }

    /** @param Bind $context */
    public static function install(Application $app, \Reflector $reflection, $context = null): void
    {
        if (! $context) {
            return;
        }

        if (! $reflection instanceof \ReflectionClass) {
            throw new \Exception("You can only bind classes");
        }

        $app->bind(
            BindingType::BIND,
            $reflection->getName(),
            $reflection->getName(),
            $context->aliases
        );
    }
}
