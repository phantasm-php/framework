<?php

namespace Phantasm\Container;

use Phantasm\Contracts\Container\Binding as BindingContract;
use Phantasm\Contracts\Container\Container as ContainerContract;

class Container implements ContainerContract
{
    /** @var array<string, array{BindingContract, mixed}> */
    protected array $bindings = [];

    /** @var array<string, mixed> */
    protected array $instances = [];

    /** @var array<string, mixed> */
    protected array $scoped = [];

    /** @var array<string, string> */
    protected array $aliases = [];

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]);
    }

    public function get(string $id, bool $resolve = false): mixed
    {
        if (isset($this->aliases[$id])) {
            $id = $this->aliases[$id];
        }

        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (isset($this->scoped[$id])) {
            return $this->scoped[$id];
        }

        if (! isset($this->bindings[$id])) {
            return $resolve
                ? $this->resolve($id)
                : throw new \InvalidArgumentException("Container doesn't have a binding for `{$id}`");
        }

        [$binding, $instance] = $this->bindings[$id];

        if ($instance instanceof \Closure) {
            $instance = $instance($this);
        } elseif (is_object($instance)) {
            return $instance;
        }

        if (is_string($instance)) {
            $instance = $this->resolve($instance);
        }

        return match ($binding) {
            Binding::SINGLETON => $this->instances[$id] = $instance,
            Binding::SCOPED => $this->scoped[$id] = $instance,
            default => $instance,
        };
    }

    public function set(BindingContract $binding, string $id, $instance = null, array $aliases = []): void
    {
        $this->bindings[$id] = [$binding, $instance ?? $id];

        $aliases = array_map(static fn () => $id, array_flip($aliases));

        $this->aliases = array_merge($this->aliases, $aliases);
    }

    public function resolve(string $id): mixed
    {
        if (! class_exists($id)) {
            throw new \InvalidArgumentException("Can't resolve binding for `{$id}`. Class doesn't exist.");
        }

        $reflection = new \ReflectionClass($id);
        $parameters = $reflection->getConstructor()?->getParameters() ?? [];
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependencies[] = $this->get($parameter->getType(), true);
        }

        return $reflection->newInstanceArgs($dependencies);
    }
}
