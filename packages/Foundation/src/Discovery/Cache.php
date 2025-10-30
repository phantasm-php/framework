<?php

namespace Phantasm\Foundation\Discovery;

use Composer\Script\Event;
use Phantasm\Contracts\Foundation\Provider;

class Cache
{
    public static function path(string $root): string
    {
        return implode(DIRECTORY_SEPARATOR, [$root, 'vendor', 'composer', 'phantasm', 'autoload.php']);
    }

    public static function postAutoloadDump(Event $event): void
    {
        $root = dirname($event->getComposer()->getConfig()->get('vendor-dir'));
        $cachePath = static::path($root);

        if (! file_exists($dir = dirname($cachePath))) {
            mkdir($dir, 0755, true);
        }

        $references = [];

        foreach (Finder::fromPackages($root) ?? [] as $path => $class) {
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

    public static function shouldInstall(\ReflectionClass $reflection): bool
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
