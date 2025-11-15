<?php

namespace Phantasm\Foundation;

use Phantasm\Container\Binding;
use Phantasm\Container\Container;
use Phantasm\Contracts\Container\Container as ContainerContract;
use Phantasm\Contracts\Foundation\Extension;

class Application
{
    protected static ContainerContract $container;

    protected function __construct()
    {
        new Discovery\Finder(static::$container)
            ->scan(Extension::class, true);
    }

    public function container(): ContainerContract
    {
        return static::$container;
    }

    public function run()
    {
        return match (php_sapi_name()) {
            'cli' => 'CLI',
            default => 'WEB',
        };
    }

    public static function instance(): static
    {
        if (! isset(static::$container)) {
            static::$container = new Container();
            static::$container->set(Binding::SINGLETON, static::class, new static);
        }

        return static::$container->get(static::class);
    }

    public static function running(): bool
    {
        return isset(static::$container);
    }

    public static function unload(): void
    {
        unset(static::$container);
    }
}
