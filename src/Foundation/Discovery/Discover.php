<?php

namespace WeStacks\Framework\Foundation\Discovery;

use WeStacks\Framework\Contracts\Foundation\Application;
use WeStacks\Framework\Contracts\Foundation\Discovery\Discover as DiscoverContract;

class Discover implements DiscoverContract
{
    protected array $discovered = [];

    public function __construct(
        protected string $root,
        Application $container
    ) {
        if (! file_exists($cachePath = $this->cachePath())) {
            $this->discover($container);
            return;
        }

        $this->discovered = array_map(
            static fn (array $entries) => array_map('unserialize', $entries),
            require_once $cachePath,
        );

        foreach ($this->discovered as $discoverables) {
            foreach ($discoverables as $discoverable) {
                if ($discoverable instanceof Installable) {
                    $discoverable->install($container);
                }
            }
        }
    }

    protected function cachePath()
    {
        return implode(DIRECTORY_SEPARATOR, [$this->root, 'service', 'discover.php']);
    }

    public function cache(): void
    {
        $references = var_export(array_map(
            static fn ($entries) => array_map('serialize', $entries),
            $this->discovered
        ), true);

        file_put_contents($this->cachePath(), "<?php return {$references};");
    }

    private function discover(Application $container)
    {
        foreach ($this->composerDiscover($this->root) as $class) {
            if (! class_exists($class) && ! interface_exists($class)) {
                continue;
            }

            $reflection = new \ReflectionClass($class);

            $this->scan($reflection, $container);

            foreach ($reflection->getMethods() as $method) {
                $this->scan($method, $container);
            }

            foreach ($reflection->getProperties() as $property) {
                $this->scan($property, $container);
            }
        }
    }

    private function scan(\Reflector $reflection, Application $container)
    {
        foreach ($reflection->getAttributes() as $attribute) {
            $name = $attribute->getName();

            if (is_subclass_of($name, Discoverable::class)) {
                /** @var Discoverable */
                $instance = $attribute->newInstance();

                $instance->setSource($reflection);

                $this->discovered[$name][] = $instance;

                if ($instance instanceof Installable) {
                    $instance->install($container);
                }
            }
        }
    }

    private function composerDiscover(string $appRoot)
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

                yield from $this->discoverFrom($root, $package['autoload']['psr-4']);
            }
        }

        $source = json_decode(file_get_contents($appRoot . DIRECTORY_SEPARATOR . 'composer.json'), true);

        yield from $this->discoverFrom($appRoot, $source['autoload']['psr-4']);
    }

    private function discoverFrom(string $root, array $paths)
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

                    yield (class_exists($class) || interface_exists($class) ? $class : $file);
                }
            }
        }
    }
}
