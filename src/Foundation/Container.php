<?php

namespace WeStacks\Framework\Foundation;

use WeStacks\Framework\Contracts\Foundation\BindingType as BindingTypeContract;

trait Container
{
    protected array $bindings = [];
    protected array $instances = [];
    protected array $aliases = [];
    protected array $scoped = [];

    public function bind(BindingTypeContract $type, string $abstract, callable|string|object|null $concrete = null, array $aliases = []): void
    {
        $this->bindings[$abstract] = [$concrete ?? $abstract, $type];

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
        } elseif (isset($this->scoped[$abstract])) {
            return $this->scoped[$abstract];
        }

        if (isset($this->aliases[$abstract])) {
            $abstract = $this->aliases[$abstract];
        }

        if (! $this->has($abstract)) {
            return class_exists($abstract) ? $this->resolve(new \ReflectionClass($abstract)) : null;
        }

        [$concrete, $type] = $this->bindings[$abstract];

        if ($concrete instanceof \Closure) {
            $concrete = $concrete($this);
        } elseif (is_object($concrete)) {
            return $concrete;
        }

        if (is_string($concrete)) {
            $concrete = $this->resolve(new \ReflectionClass($concrete));
        }

        match ($type) {
            BindingType::SINGLETON => $this->instances[$abstract] = $concrete,
            BindingType::SCOPED => $this->scoped[$abstract] = $concrete,
            default => null,
        };

        if (isset($this->bindings[$abstract]) && $type === BindingType::SINGLETON) {
            $this->instances[$abstract] = $concrete;
        }

        return $concrete;
    }

    protected function resolve(\Reflector $concrete): mixed
    {
        if ($concrete instanceof \ReflectionClass) {
            $parameters = $concrete->getConstructor()?->getParameters() ?? [];
            $dependencies = [];

            foreach ($parameters as $parameter) {
                $dependencies[] = $this->get($parameter->getType());
            }

            return $concrete->newInstanceArgs(array_filter($dependencies));
        }

        throw new \Exception('You can only resolve classes');
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]);
    }

    public function flush(): void
    {
        $this->scoped = [];
    }
}
