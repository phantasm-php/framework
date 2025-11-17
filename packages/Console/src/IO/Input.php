<?php

namespace Phantasm\Console\IO;

class Input
{
    /**
     * Console command
     */
    readonly public string $command;

    /**
     * Console arguments
     */
    readonly public array $arguments;

    /**
     * Console options
     */
    readonly public array $options;

    /**
     * Short options mapping
     */
    protected array $shortMap = [];

    public function __construct(?array $arguments = null)
    {
        if (! is_null($arguments)) {
            $argc = count($arguments);
            $argv = $arguments;
        } else {
            $argc = $_SERVER['argc'];
            $argv = $_SERVER['argv'];
        }

        $this->command = array_shift($argv);
        $options = $arguments = [];


        for($i = 0; $i < count($argv); $i++) {
            $arg = $argv[$i];

            if (preg_match('/^--([^=]+)=(.*)$/', $arg, $matches)) {
                $options[$matches[1]] = $matches[2];
            } elseif (preg_match('/^--(.+)$/', $arg, $matches)) {
                $options[$matches[1]] = 1;
            } elseif (preg_match('/^-([a-zA-Z]+)$/', $arg, $matches)) {
                $flags = str_split($matches[1]);

                if (count($flags) === 1) {
                    if (isset($argv[$i + 1]) && !str_starts_with($argv[$i + 1], '-')) {
                        $options[$flags[0]] = $argv[++$i];
                    } else {
                        $options[$flags[0]] = isset($options[$flags[0]])
                            ? $options[$flags[0]] + 1
                            : 1;
                    }
                } else {
                    foreach ($flags as $flag) {
                        $options[$flag] = isset($options[$flag])
                            ? $options[$flag] + 1
                            : 1;
                    }
                }
            } else {
                $arguments[] = $arg;
            }
        }

        $this->arguments = $arguments;
        $this->options = $options;
    }

    /**
     * @internal
     */
    public function useShortMap(array $map): void
    {
        $this->shortMap = $map;
    }

    public function argument(int $index, mixed $default = null): mixed
    {
        return $this->arguments[$index] ?? $default;
    }

    public function option(string $name, mixed $default = null): mixed
    {
        if (isset($this->shortMap[$name])) {
            $name = $this->shortMap[$name];
        }

        return $this->options[$name] ?? $default;
    }

    public function hasOption(string $name): bool
    {
        if (isset($this->shortMap[$name])) {
            $name = $this->shortMap[$name];
        }

        return isset($this->options[$name]);
    }
}
