<?php

namespace Framework\Core;

use Dotenv\Dotenv;
use Psr\Container\ContainerInterface;

class Application implements ContainerInterface
{
    protected static ?self $instance;

    protected array $bindings = [];

    protected array $instances = [];

    protected function __construct(protected string $root)
    {
        Dotenv::createImmutable($this->root)->safeLoad();
        Discover::load($this->root);
    }

    public static function make(string $root): static
    {
        return static::$instance ??= new static($root);
    }

    public static function instance(): ?static
    {
        return static::$instance ?? null;
    }

    public function http()
    {
        return new HttpKernel();
    }

    public function console()
    {
        return new ConsoleKernel();
    }

    public function bind(string $id, bool $scoped, callable|string|null $concrete = null): void
    {
        $concrete ??= $id;

        $this->bindings[$id] = [$concrete, $scoped];
    }

    public function flush(): void
    {
        $this->instances = [];
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]);
    }

    public function get(string $id)
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (! isset($this->bindings[$id])) {
            return class_exists($id) ? new $id($this) : throw new \Exception('Class ' . $id . ' not found');
        }

        [$concrete, $scoped] = $this->bindings[$id];

        if (is_string($concrete)) {
            $concrete = class_exists($concrete) ? new $concrete($this) : throw new \Exception('Class ' . $concrete . ' not found');
        } else {
            $concrete = $concrete($this);
        }

        if ($scoped) {
            $this->instances[$id] = $concrete;
        }

        return $concrete;
    }
}
