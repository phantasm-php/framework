<?php

namespace WeStacks\Framework\Foundation;

use Dotenv\Dotenv;
use WeStacks\Framework\Contracts\Foundation\Application as ApplicationContract;
use WeStacks\Framework\Contracts\Foundation\Discovery\Discover as DiscoverContract;
use WeStacks\Framework\Foundation\Discovery\Discover;

class Application implements ApplicationContract
{
    use Container;

    protected static ApplicationContract $instance;

    protected function __construct(protected string $root)
    {
        define('FRAMEWORK_START', microtime(true));

        Dotenv::createUnsafeImmutable($root)->safeLoad();

        $this->bind(
            ApplicationContract::class,
            $this,
            true,
            [Application::class],
        );
    }

    public static function instance(?string $root = null): static
    {
        return static::$instance ??= new static($root);
    }

    public function run(): void
    {
        $this->bind(
            DiscoverContract::class,
            new Discover($this->root, $this),
            true,
            [Discover::class],
        );

        if (php_sapi_name() === 'cli') {
            echo 'CLI';
        } else {
            echo 'WEB';
        }
    }
}
