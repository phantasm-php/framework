<?php

namespace WeStacks\Framework\Core;

use Dotenv\Dotenv;
use WeStacks\Framework\Container\Attributes\Singleton;
use WeStacks\Framework\Discovery\Discover;
use WeStacks\Framework\Container\Container;
use WeStacks\Framework\Contracts\Core\Application as ApplicationInterface;
use WeStacks\Framework\Contracts\Discovery\Discover as DiscoverContract;

#[Singleton(ApplicationInterface::class)]
class Application implements ApplicationInterface
{
    const VERSION = 'dev-main';

    public static function make(string $root): static
    {
        Dotenv::createImmutable($root)->safeLoad();

        $container = Container::instance();

        $container->bind(
            DiscoverContract::class,
            new Discover($root),
            true,
            [Discover::class],
        );

        return $container->get(ApplicationInterface::class);
    }

    public function http()
    {
        return new HttpKernel();
    }

    public function console()
    {
        return new ConsoleKernel(getenv('APP_NAME') ?: 'WeStacks', self::VERSION);
    }
}
