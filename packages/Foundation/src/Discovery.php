<?php

namespace Phantasm\Foundation;

use Phantasm\Container\Container;
use Phantasm\Contracts\Foundation\Extension;

class Discovery
{
    public function __construct(
        protected Container $container,
    ) {}

    public function scan(string $root, bool $preferCache = true)
    {
        $callbacks = [];

        // TODO: Cache this after dump-autoload. Maybe load anonymous classes?
        foreach ($this->fromPackages($root) ?? [] as $path => $class) {
            if (! $class) {
                continue;
            }

            $this->load(new \ReflectionClass($class), $callbacks);
        }

        foreach ($this->fromProject($root) ?? [] as $path => $class) {
            if (! $class) {
                continue;
            }

            $this->load(new \ReflectionClass($class), $callbacks);
        }

        foreach ($callbacks as $callback) {
            call_user_func($callback);
        }
    }

    protected function fromProject(string $root)
    {
        $source = implode(DIRECTORY_SEPARATOR, [$root, 'composer.json']);
        $source = json_decode(file_get_contents($source), true);

        $psr4 = array_map(
            static fn (string $path) => implode(DIRECTORY_SEPARATOR, [$root, $path]),
            $source['autoload']['psr-4']
        );

        return $this->map($psr4);
    }

    protected function fromPackages(string $root)
    {
        $source = implode(DIRECTORY_SEPARATOR, [$root, 'vendor', 'composer', 'installed.json']);
        $source = json_decode(file_get_contents($source), true);

        foreach ($source['packages'] as $package) {
            if (! isset($package['extra']['phantasm']['discover']) || ! $package['extra']['phantasm']['discover']) {
                continue;
            }

            $pkgRoot = implode(DIRECTORY_SEPARATOR, [$root, 'vendor', 'composer', $package['install-path']]);
            $psr4 = array_map(
                static fn (string $path) => implode(DIRECTORY_SEPARATOR, [$pkgRoot, $path]),
                $package['autoload']['psr-4']
            );

            yield from $this->map($psr4);
        }
    }

    protected function map(array $psr4)
    {
        foreach ($psr4 as $namespace => $path) {
            foreach (is_array($path) ? $path : [$path] as $_path) {
                if (! is_dir($_path)) {
                    continue;
                }

                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($_path, \RecursiveDirectoryIterator::SKIP_DOTS),
                );

                foreach ($iterator as $file) {
                    if ($file->getExtension() !== 'php') {
                        continue;
                    }

                    $relative = str_replace($path, '', $file->getPathname());
                    $class = $namespace . str_replace(DIRECTORY_SEPARATOR, '\\', substr($relative, 0, -4));

                    yield $file->getPathname() => class_exists($class) || interface_exists($class) ? $class : null;
                }
            }
        }
    }

    protected function load(\ReflectionClass $class, array &$callbacks)
    {
        $this->tryInstall($class, $callbacks);

        foreach ($class->getMethods() as $method) {
            $this->tryInstall($method, $callbacks);
        }

        foreach ($class->getProperties() as $property) {
            $this->tryInstall($property, $callbacks);
        }
    }

    /**
     * @param  \Reflector|\ReflectionMethod|\ReflectionProperty|\ReflectionClass  $reflection
     */
    protected function tryInstall(\Reflector $reflection, array &$callbacks, bool $dryRun = false)
    {
        foreach ($reflection->getAttributes() as $attribute) {
            /** @var Extension $instance */
            $instance = $attribute->newInstance();

            if ($instance instanceof Extension) {
                if ($dryRun) {
                    return true;
                }

                $instance->register($this->container, $reflection, $instance);
                array_push($callbacks, fn () => $instance->boot($this->container, $reflection, $instance));
            }
        }

        if ($reflection instanceof \ReflectionClass && $reflection->implementsInterface(Extension::class) && $reflection->getName() !== Extension::class) {
            if ($dryRun) {
                return true;
            }

            /** @var class-string<Extension> */
            $class = $reflection->getName();
            $class::register($this->container, $reflection, null);
            array_push($callbacks, fn () => $class::boot($this->container, $reflection, null));
        }
    }
}
