<?php

namespace Phantasm\Foundation;

use Phantasm\Contracts\Foundation\Application;
use Phantasm\Contracts\Foundation\Discovery\Bootable;
use Phantasm\Contracts\Foundation\Discovery\Installable;

/**
 * @mixin Application
 */
trait Discover
{
    protected function discover()
    {
        $boot = [];

        foreach ($this->composerDiscover($this->root) as $class) {
            if (! class_exists($class) && ! interface_exists($class)) {
                continue;
            }

            $reflection = new \ReflectionClass($class);

            $this->scan($reflection, $this, $boot);

            foreach ($reflection->getMethods() as $method) {
                $this->scan($method, $this, $boot);
            }

            foreach ($reflection->getProperties() as $property) {
                $this->scan($property, $this, $boot);
            }
        }

        foreach ($boot as $bootable) {
            $bootable();
        }
    }

    /**
     * @param \Reflector|\ReflectionMethod|\ReflectionProperty|\ReflectionClass $reflection
     */
    private function scan(\Reflector $reflection, Application $container, array &$boot = [])
    {
        foreach ($reflection->getAttributes() as $attribute) {
            /** @var Installable|Bootable $instance */
            $instance = $attribute->newInstance();

            if ($instance instanceof Installable) {
                $instance->install($container, $reflection, $instance);
            }

            if ($instance instanceof Bootable) {
                $boot[] = fn () => $instance->boot($container, $reflection, $instance);
            }
        }

        if (! $reflection instanceof \ReflectionClass || $reflection->isInterface()) {
            return;
        }

        if ($reflection->implementsInterface(Installable::class)) {
            /** @var class-string<Installable> */
            $class = $reflection->getName();
            $class::install($container, $reflection);
        }

        if ($reflection->implementsInterface(Bootable::class)) {
            /** @var class-string<Bootable> */
            $class = $reflection->getName();
            $boot[] = fn () => $class::boot($container, $reflection);
        }
    }

    private function composerDiscover(string $appRoot)
    {
        $source = implode(DIRECTORY_SEPARATOR, [$appRoot, 'vendor', 'composer', 'installed.json']);
        $source = json_decode(file_get_contents($source), true);

        foreach ($source['packages'] as $package) {
            if (! isset($package['extra']['framework'])) {
                continue;
            }

            $meta = $package['extra']['framework'];

            if (isset($meta['discover']) && $meta['discover']) {
                $root = implode(DIRECTORY_SEPARATOR, [$appRoot, 'vendor', 'composer', $package['install-path']]);

                yield from $this->discoverFrom($root, $package['autoload']['psr-4']);
            }
        }

        $source = json_decode(file_get_contents($appRoot . DIRECTORY_SEPARATOR . 'composer.json'), true);

        yield from $this->discoverFrom($appRoot, $source['autoload']['psr-4']);
    }

    private function discoverFrom(string $root, array $paths)
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
                    new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
                );

                foreach ($iterator as $file) {
                    /** @var \SplFileInfo $file */
                    if ($file->getExtension() !== 'php') {
                        continue;
                    }

                    $relative = str_replace($path, '', $file->getPathname());
                    $class = $namespace . str_replace(DIRECTORY_SEPARATOR, '\\', substr($relative, 0, -4));

                    yield class_exists($class) || interface_exists($class) ? $class : $file;
                }
            }
        }
    }
}
