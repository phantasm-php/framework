<?php

namespace Phantasm\Foundation\Attributes;

use Phantasm\Contracts\Foundation\Application;
use Phantasm\Contracts\Foundation\Discovery\Installable;
use Phantasm\Foundation\BindingType;

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
            throw new \Exception('You can only bind classes');
        }

        $app->bind(
            BindingType::BIND,
            $reflection->getName(),
            $reflection->getName(),
            $context->aliases,
        );
    }
}
