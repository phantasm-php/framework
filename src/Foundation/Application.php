<?php

namespace WeStacks\Framework\Foundation;

use Dotenv\Dotenv;
use WeStacks\Framework\Contracts\Foundation\Application as ApplicationContract;

class Application implements ApplicationContract
{
    use Container,
        Discover;

    protected static ApplicationContract $instance;

    protected function __construct(public readonly string $root)
    {
        define('FRAMEWORK_START', microtime(true));

        Dotenv::createUnsafeImmutable($root)->safeLoad();

        $this->bind(
            BindingType::SINGLETON,
            Application::class,
            $this,
            [ApplicationContract::class],
        );
    }

    public static function instance(?string $root = null): static
    {
        return static::$instance ??= new static($root);
    }

    public function run(): void
    {
        $this->discover();

        $kernel = match (php_sapi_name()) {
            'cli' => $this->get(\WeStacks\Framework\Contracts\Console\Kernel::class),
            default => $this->get(\WeStacks\Framework\Contracts\Http\Kernel::class),
        };

        $kernel->handle();
    }
}
