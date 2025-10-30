<?php

namespace Phantasm\Foundation;

use Phantasm\Container\Binding;
use Phantasm\Container\Container;
use Phantasm\Contracts\Container\Container as ContainerContract;

class Application
{
    protected static ?self $instance = null;
    public readonly ContainerContract $container;

    protected function __construct()
    {
        define('PHANTASM_START', microtime(true));

        $this->container = new Container();

        $this->container->set(Binding::SINGLETON, static::class, $this);

        new Discovery($this->container)->scan();
    }

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    public static function unload(): void
    {
        self::$instance = null;
    }
}
