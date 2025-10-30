<?php

namespace Phantasm\Foundation;

use Phantasm\Container\Binding;
use Phantasm\Container\Container;
use Phantasm\Contracts\Container\Container as ContainerContract;

class Application
{
    protected static ?self $instance = null;
    public readonly ContainerContract $container;

    protected function __construct(
        public readonly string $root,
    ) {
        define('PHANTASM_START', microtime(true));

        $this->container = new Container();

        $this->container->set(static::class, $this, Binding::SINGLETON);

        new Discovery($this->container)->scan($this->root);
    }

    public static function instance(?string $root = null): self
    {
        return self::$instance ??= new self($root);
    }

    public static function unload(): void
    {
        self::$instance = null;
    }
}
