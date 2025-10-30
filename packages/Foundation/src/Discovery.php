<?php

namespace Phantasm\Foundation;

use Composer\InstalledVersions;
use Composer\Script\Event;
use Phantasm\Container\Container;
use Phantasm\Contracts\Foundation\Provider;

class Discovery
{
    public function __construct(
        protected Container $container,
    ) {}

    public static function cache(Event $event)
    {
        $root = dirname($event->getComposer()->getConfig()->get('vendor-dir'));
        $cachePath = implode(DIRECTORY_SEPARATOR, [$root, 'vendor', 'composer', 'phantasm', 'autoload.php']);

        if (! file_exists($dir = dirname($cachePath))) {
            mkdir($dir, 0755, true);
        }

        $references = [];

        foreach (static::fromPackages($root) ?? [] as $path => $class) {
            if (! $class) {
                continue;
            }

            if (static::shouldInstall(new \ReflectionClass($class))) {
                $references[$path] = $class;
            }
        }

        file_put_contents($cachePath, '<?php return ' . var_export($references, true) . ';');

        // TODO: add reporting
    }

    public function scan(bool $preferCache = true)
    {
        $callbacks = [];
        $root = InstalledVersions::getRootPackage()['install_path'];

        if ($preferCache && file_exists($cachePath = implode(DIRECTORY_SEPARATOR, [$root, 'vendor', 'composer', 'phantasm', 'autoload.php']))) {
            $references = require $cachePath;

            foreach ($references as $path => $class) {
                static::load(new \ReflectionClass($class), $callbacks);
            }
        } else foreach (static::fromPackages($root) ?? [] as $path => $class) {
            if (! $class) {
                continue;
            }

            static::load(new \ReflectionClass($class), $callbacks);
        }

        foreach (static::fromProject($root) ?? [] as $path => $class) {
            if (! $class) {
                continue;
            }

            static::load(new \ReflectionClass($class), $callbacks);
        }

        foreach ($callbacks as $callback) {
            call_user_func($callback);
        }
    }

    protected static function fromProject(string $root)
    {
        $source = implode(DIRECTORY_SEPARATOR, [$root, 'composer.json']);
        $source = json_decode(file_get_contents($source), true);

        $psr4 = array_map(
            static fn (string $path) => implode(DIRECTORY_SEPARATOR, [$root, $path]),
            $source['autoload']['psr-4']
        );

        return static::map($psr4);
    }

    protected static function fromPackages(string $root)
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

            yield from static::map($psr4);
        }
    }

    protected static function map(array $psr4)
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
        static::tryInstall($class, $callbacks);

        foreach ($class->getMethods() as $method) {
            static::tryInstall($method, $callbacks);
        }

        foreach ($class->getProperties() as $property) {
            static::tryInstall($property, $callbacks);
        }
    }

    /**
     * @param  \Reflector|\ReflectionMethod|\ReflectionProperty|\ReflectionClass  $reflection
     */
    protected function tryInstall(\Reflector $reflection, array &$callbacks)
    {
        foreach ($reflection->getAttributes() as $attribute) {
            /** @var Provider $instance */
            $instance = $attribute->newInstance();

            if ($instance instanceof Provider) {
                $instance->register($this->container, $reflection, $instance);
                array_push($callbacks, fn () => $instance->boot($this->container, $reflection, $instance));
            }
        }

        if ($reflection instanceof \ReflectionClass
            && $reflection->implementsInterface(Provider::class)
            && $reflection->getName() !== Provider::class
            && !count($reflection->getAttributes(\Attribute::class))
        ) {
            /** @var class-string<Provider> $class */
            $class = $reflection->getName();
            $class::register($this->container, $reflection, null);
            array_push($callbacks, fn () => $class::boot($this->container, $reflection, null));
        }
    }

    protected static function shouldInstall(\ReflectionClass $reflection): bool
    {
        foreach ($reflection->getAttributes() as $attribute) {
            /** @var Provider $instance */
            $instance = $attribute->newInstance();

            if ($instance instanceof Provider) {
                return true;
            }
        }

        if ($reflection instanceof \ReflectionClass
            && $reflection->implementsInterface(Provider::class)
            && $reflection->getName() !== Provider::class
            && !count($reflection->getAttributes(\Attribute::class))
        ) {
            return true;
        }

        return false;
    }
}
