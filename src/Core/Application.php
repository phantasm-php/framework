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
    }

    public static function make(string $root): static
    {
        return static::$instance ??= new static($root);
    }

    public static function instance(): ?static
    {
        return static::$instance ?? null;
    }

    public function run(Scope $scope): void
    {
        $app = match ($scope) {
            Scope::CONSOLE => new ConsoleApplication(),
            default => throw new \Exception('Unknown scope ' . $scope)
        };

        foreach ($this->discover() as $entry) {
            //
        }

        $app->run();
    }

    protected function bind(string $id, bool $scoped, callable|string $concrete): void
    {
        $this->bindings[$id] = [$concrete, $scoped];
    }

    protected function flush(): void
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

    /**
     * @return \Generator<mixed, array{0: string, 1: \SplFileInfo}, mixed, void>
     */
    protected function discover(): \Generator
    {
        $source = implode(DIRECTORY_SEPARATOR, [$this->root, 'vendor', 'composer', 'installed.json']);

        $source = json_decode(file_get_contents($source), true);

        foreach ($source['packages'] as $package) {
            if (! isset($package['extra']['framework'])) {
                continue;
            }

            $meta = $package['extra']['framework'];

            if (isset($meta['discover']) && $meta['discover'] === true) {
                $root = implode(DIRECTORY_SEPARATOR, [$this->root, 'vendor', 'composer', $package['install-path']]);

                yield from $this->discoverFrom($root, $package['autoload']['psr-4']);
            }
        }

        $source = json_decode(file_get_contents($this->root . DIRECTORY_SEPARATOR . 'composer.json'), true);

        yield from $this->discoverFrom($this->root, $source['autoload']['psr-4']);
    }

    protected function discoverFrom(string $root, array $paths)
    {
        foreach ($paths as $namespace => $_path) {
            if (! is_array($_path)) {
                $_path = [$_path];
            }

            foreach ($_path as $path) {
                if (! is_dir($path = $root . DIRECTORY_SEPARATOR . $path)) {
                    continue;
                }

                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
                );

                foreach ($iterator as $file) {
                    /** @var \SplFileInfo $file */
                    if ($file->getExtension() !== 'php') {
                        continue;
                    }

                    $relative = str_replace($path, '', $file->getPathname());
                    $class = $namespace . str_replace(DIRECTORY_SEPARATOR, '\\', substr($relative, 0, -4));

                    yield (class_exists($class) ? $class : $file);
                }
            }
        }
    }
}
