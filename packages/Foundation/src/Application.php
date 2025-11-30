<?php

namespace Phantasm\Foundation;

use Phantasm\Container\Binding;
use Phantasm\Container\Container;
use Phantasm\Contracts\Console\Kernel as ConsoleKernel;
use Phantasm\Contracts\Container\Container as ContainerContract;
use Phantasm\Contracts\Foundation\Application as ApplicationContract;
use Phantasm\Contracts\Foundation\Extension;

/**
 * @mixin ContainerContract
 */
class Application implements ApplicationContract
{
    protected static ContainerContract $container;

    protected function __construct()
    {
        new Discovery\Finder(static::$container)->scan(Extension::class, true);
    }

    public function __call($name, $arguments)
    {
        return static::$container->{$name}(...$arguments);
    }

    public function run(): void
    {
        echo match (php_sapi_name()) {
            'cli' => $this->get(ConsoleKernel::class)->handle(),
            default => 'WEB',
        };
    }

    public static function instance(): static
    {
        if (!isset(static::$container)) {
            static::$container = new Container();
            static::$container->set(Binding::SINGLETON, ApplicationContract::class, new static(), [static::class]);
        }

        return static::$container->get(ApplicationContract::class);
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
