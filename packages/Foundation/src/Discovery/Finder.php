<?php

namespace Phantasm\Foundation\Discovery;

use Composer\InstalledVersions;
use Phantasm\Container\Container;
use Phantasm\Contracts\Foundation\Extension;

class Finder
{
    public function __construct(
        protected Container $container,
    ) {}

    public function scan(string $search, bool $preferCache)
    {
        $callbacks = [];
        $root = InstalledVersions::getRootPackage()['install_path'];

        if ($preferCache && \file_exists($cachePath = Cache::path($root))) {
            $references = require $cachePath;

            foreach ($references as $path => $class) {
                $callbacks += static::load(new \ReflectionClass($class), $search);
            }
        } else {
            foreach (static::fromPackages($root) ?? [] as $path => $class) {
                if (!$class) {
                    continue;
                }

                $callbacks += static::load(new \ReflectionClass($class), $search);
            }
        }

        foreach (static::fromProject($root) ?? [] as $path => $class) {
            if (!$class) {
                continue;
            }

            $callbacks += static::load(new \ReflectionClass($class), $search);
        }

        foreach ($callbacks as $callback) {
            \call_user_func($callback);
        }
    }

    protected static function fromProject(string $root)
    {
        $source = \implode(DIRECTORY_SEPARATOR, [$root, 'composer.json']);
        $source = \json_decode(\file_get_contents($source), true);

        $psr4 = \array_map(static fn(string $path) => \implode(DIRECTORY_SEPARATOR, [
            $root,
            $path,
        ]), $source['autoload']['psr-4']);

        return static::map($psr4);
    }

    public static function fromPackages(string $root)
    {
        $source = \implode(DIRECTORY_SEPARATOR, [$root, 'vendor', 'composer', 'installed.json']);
        $source = \json_decode(\file_get_contents($source), true);

        foreach ($source['packages'] as $package) {
            if (!isset($package['extra']['phantasm']['discover']) || !$package['extra']['phantasm']['discover']) {
                continue;
            }

            $pkgRoot = \implode(DIRECTORY_SEPARATOR, [$root, 'vendor', 'composer', $package['install-path']]);
            $psr4 = \array_map(static fn(string $path) => \implode(DIRECTORY_SEPARATOR, [
                $pkgRoot,
                $path,
            ]), $package['autoload']['psr-4']);

            yield from static::map($psr4);
        }
    }

    protected static function map(array $psr4)
    {
        foreach ($psr4 as $namespace => $path) {
            foreach (\is_array($path) ? $path : [$path] as $_path) {
                if (!\is_dir($_path)) {
                    continue;
                }

                $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(
                    $_path,
                    \RecursiveDirectoryIterator::SKIP_DOTS,
                ));

                foreach ($iterator as $file) {
                    if ($file->getExtension() !== 'php') {
                        continue;
                    }

                    $relative = \str_replace($path, '', $file->getPathname());
                    $class = $namespace . \str_replace(DIRECTORY_SEPARATOR, '\\', \substr($relative, 0, -4));

                    yield $file->getPathname() => \class_exists($class) || \interface_exists($class) ? $class : null;
                }
            }
        }
    }

    protected function load(\ReflectionClass $class, string $search): array
    {
        $callbacks = $this->tryInstall($class, $search);

        foreach ($class->getMethods() as $method) {
            $callbacks += $this->tryInstall($method, $search);
        }

        foreach ($class->getProperties() as $property) {
            $callbacks += $this->tryInstall($property, $search);
        }

        return $callbacks;
    }

    /**
     * @param \Reflector|\ReflectionMethod|\ReflectionProperty|\ReflectionClass $reflection
     * @param class-string $search
     */
    protected function tryInstall(\Reflector $reflection, string $search): array
    {
        $callbacks = [];

        foreach ($reflection->getAttributes($search, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            /** @var Extension $instance */
            $instance = $attribute->newInstance();
            $callbacks[] = $instance->install($this->container, $reflection, $instance);
        }

        if (
            $reflection instanceof \ReflectionClass
            && $reflection->implementsInterface($search)
            && $reflection->getName() !== $search
            && !\count($reflection->getAttributes(\Attribute::class))
        ) {
            /** @var class-string<Extension> $class */
            $class = $reflection->getName();
            $callbacks[] = $class::install($this->container, $reflection, null);
        }

        return \array_values(\array_filter($callbacks));
    }
}
