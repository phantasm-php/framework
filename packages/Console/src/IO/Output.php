<?php

namespace Phantasm\Console\IO;

/**
 * @method self info(string $message, bool $eol = true)
 * @method self success(string $message, bool $eol = true)
 * @method self warning(string $message, bool $eol = true)
 * @method self error(string $message, bool $eol = true)
 * @method self comment(string $message, bool $eol = true)
 */
class Output
{
    const INFO = [Format::CYAN];
    const SUCCESS = [Format::GREEN];
    const WARNING = [Format::YELLOW];
    const ERROR = [Format::RED];
    const COMMENT = [Format::ITALIC, Format::DIM];

    protected $stream;
    protected bool $decorated;
    public readonly array $styles;

    public function __construct(
        $stream = STDOUT,
        ?bool $decorated = null,
        array $styles = []
    ) {
        $this->stream = $stream;
        $this->decorated = $decorated ?? $this->hasColorSupport();
        $this->styles = $styles;
    }

    /**
     * Write a message to output stream using predefined style or custom formatting
     */
    public function write(string $message, bool $eol = true, array $style = []): self
    {
        $message = $this->decorated
            ? Format::apply($message, ...$style)
            : preg_replace('/\033\[[0-9;]*m/', '', $message);

        fwrite($this->stream, $message . ($eol ? PHP_EOL : ''));

        return $this;
    }

    public function __call(string $name, array $arguments)
    {
        $style = isset($this->styles[$name]) ? $this->styles[$name] : self::{strtoupper($name)};

        return $this->write(...$arguments, style: $style);
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
}
