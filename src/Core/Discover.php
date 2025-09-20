<?php

namespace Framework\Core;

abstract class Discover
{
    private static array $discovered = [];

    public static function load(string $root)
    {
        $cachePath = implode(DIRECTORY_SEPARATOR, [$root, 'service', 'cache', 'discover.php']);

        if (! file_exists($cachePath)) {
            return static::discover($root);
        }

        static::$discovered = array_map(
            static fn (array $entries) => array_map('unserialize', $entries),
            require_once $cachePath,
        );
    }

    public static function cache($root)
    {
        $cachePath = implode(DIRECTORY_SEPARATOR, [$root, 'service', 'cache', 'discover.php']);

        $references = var_export(array_map(
            static fn ($entries) => array_map('serialize', $entries),
            static::$discovered
        ), true);

        file_put_contents($cachePath, "<?php return {$references};");
    }

    private static function discover(string $root)
    {
        foreach (static::composerDiscover($root) as $class) {
            $reflection = new \ReflectionClass(class_exists($class) ? $class : require $class);

            static::scan($reflection);

            foreach ($reflection->getMethods() as $method) {
                static::scan($method);
            }

            foreach ($reflection->getProperties() as $property) {
                static::scan($property);
            }
        }
    }

    private static function scan(\Reflector $reflection)
    {
        foreach ($reflection->getAttributes() as $attribute) {
            $name = $attribute->getName();

            if (is_subclass_of($name, Discoverable::class)) {
                /** @var Discoverable */
                $instance = $attribute->newInstance();

                $instance->setSource($reflection);

                static::$discovered[$name][] = $instance;
            }
        }
    }

    private static function composerDiscover(string $appRoot)
    {
        $source = implode(DIRECTORY_SEPARATOR, [$appRoot, 'vendor', 'composer', 'installed.json']);
        $source = json_decode(file_get_contents($source), true);

        foreach ($source['packages'] as $package) {
            if (!isset($package['extra']['framework'])) {
                continue;
            }

            $meta = $package['extra']['framework'];

            if (isset($meta['discover']) && $meta['discover'] === true) {
                $root = implode(DIRECTORY_SEPARATOR, [$appRoot, 'vendor', 'composer', $package['install-path']]);

                yield from static::discoverFrom($root, $package['autoload']['psr-4']);
            }
        }

        $source = json_decode(file_get_contents($appRoot . DIRECTORY_SEPARATOR . 'composer.json'), true);

        yield from static::discoverFrom($appRoot, $source['autoload']['psr-4']);
    }

    private static function discoverFrom(string $root, array $paths)
    {
        foreach ($paths as $namespace => $_path) {
            if (!is_array($_path)) {
                $_path = [$_path];
            }

            foreach ($_path as $path) {
                if (!is_dir($path = $root . DIRECTORY_SEPARATOR . $path)) {
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
