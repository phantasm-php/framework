<?php

namespace WeStacks\Framework\Foundation;

trait Container
{
    protected array $bindings = [];
    protected array $instances = [];
    protected array $aliases = [];

    public function bind(string $abstract, callable|string|object|null $concrete = null, bool $cache = false, array $aliases = []): void
    {
        $this->bindings[$abstract] = [$concrete ?? $abstract, $cache];

        foreach ($aliases as $alias) {
            $this->aliases[$alias] = $abstract;
        }
    }

    /**
     * @template T
     * @param class-string<T> $abstract
     * @return T
     */
    public function get(string $abstract): mixed
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (isset($this->aliases[$abstract])) {
            $abstract = $this->aliases[$abstract];
        }

        [$concrete, $cache] = $this->bindings[$abstract];

        $concrete = match(true) {
            $concrete instanceof \Closure => $concrete(),
            is_string($concrete) => new $concrete,
            default => $concrete
        };

        if (isset($this->bindings[$abstract]) && $cache) {
            $this->instances[$abstract] = $concrete;
        }

        return $concrete;
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]);
    }

    public function flush(): void
    {
        $this->instances = [];
    }
}
