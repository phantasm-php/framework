<?php

namespace Phantasm\Foundation;

use Dotenv\Dotenv;
use Phantasm\Contracts\Foundation\Application as ApplicationContract;

class Application implements ApplicationContract
{
    use Container, Discover;

    protected static ApplicationContract $instance;

    protected function __construct(
        public readonly string $root,
    ) {
        define('FRAMEWORK_START', microtime(true));

        Dotenv::createUnsafeImmutable($root)->safeLoad();

        $this->bind(
            BindingType::SINGLETON,
            Application::class,
            $this,
            [ApplicationContract::class],
        );

        $this->discover();
    }

    public function version(): ?string
    {
        $composer = json_decode(file_get_contents($this->root . '/composer.json'), true);

        return $composer['version'] ?? null;
    }

    public static function instance(?string $root = null): static
    {
        return static::$instance ??= new static($root);
    }

    public function run(...$args): mixed
    {
        return match (php_sapi_name()) {
            'cli' => $this->get(\Phantasm\Contracts\Console\Kernel::class)->run(...$args),
            default => $this->get(\Phantasm\Contracts\Http\Kernel::class)->run(...$args),
        };
    }
}
