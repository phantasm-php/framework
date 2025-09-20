<?php

namespace Framework\Core;

use Framework\Contracts\Core\Application as ApplicationContract;

class Application implements ApplicationContract
{
    public function __construct(
        protected string $root
    ) {
        foreach ($this->discover() as [$class, $file]) {
            // Load discovers
        }
    }

    public function console()
    {
        //
    }

    public function http()
    {
        //
    }

    /**
     * @return \Generator<mixed, array{0: string, 1: \SplFileInfo}, mixed, void>
     */
    protected function discover(): \Generator
    {
        $path = implode(DIRECTORY_SEPARATOR, [$this->root, 'vendor', 'composer', 'installed.json']);

        $source = json_decode(file_get_contents($path), true);

        foreach ($source['packages'] as $package) {
            if (! isset($package['extra']['framework'])) {
                continue;
            }

            $root = implode(DIRECTORY_SEPARATOR, [$this->root, 'vendor', 'composer', $package['install-path']]);

            yield from $this->discoverFrom($root, $package['extra']['framework']);
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

                    yield [$class, $file];
                }
            }
        }
    }
}
