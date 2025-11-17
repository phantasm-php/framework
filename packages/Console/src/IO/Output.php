<?php

namespace Phantasm\Console\IO;

class Output
{
    protected $stream;
    protected Theme $theme;
    protected bool $decorated;

    public function __construct(
        $stream = STDOUT,
        ?Theme $theme = null,
        ?bool $decorated = null
    ) {
        $this->theme = $theme ?? new Theme();
        $this->stream = $stream;
        $this->decorated = $decorated ?? $this->hasColorSupport();
    }

    /**
     * Write a message to output
     */
    public function write(string $message, bool $eol = true): void
    {
        $output = $this->decorated ? $message : $this->stripAnsiCodes($message);
        fwrite($this->stream, $output . ($eol ? PHP_EOL : ''));
    }

    public function __call(string $name, array $arguments): void
    {
        $arguments[0] = $this->theme->apply($name, $arguments[0]);

        $this->write(...$arguments);
    }

    /**
     * Create a new line
     */
    public function eol(int $count = 1): void
    {
        $this->write(str_repeat(PHP_EOL, $count));
    }

    /**
     * Check if output is decorated (supports colors)
     */
    public function isDecorated(): bool
    {
        return $this->decorated;
    }

    /**
     * Set decoration
     */
    public function setDecorated(bool $decorated): void
    {
        $this->decorated = $decorated;
    }

    /**
     * Detect if the terminal supports colors
     */
    protected function hasColorSupport(): bool
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return getenv('ANSICON') !== false
                || getenv('ConEmuANSI') === 'ON'
                || getenv('TERM') === 'xterm'
                || getenv('TERM') === 'xterm-256color';
        }

        return function_exists('posix_isatty') && posix_isatty($this->stream);
    }

    /**
     * Strip ANSI codes from a string
     */
    protected function stripAnsiCodes(string $string): string
    {
        return preg_replace('/\033\[[0-9;]*m/', '', $string);
    }
}
