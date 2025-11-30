<?php

namespace Phantasm\Console\IO;

use Phantasm\Contracts\Console\IO\Output as OutputInterface;

/**
 * @method self info(string $message, int|bool $eol = 1)
 * @method self success(string $message, int|bool $eol = 1)
 * @method self warning(string $message, int|bool $eol = 1)
 * @method self error(string $message, int|bool $eol = 1)
 * @method self comment(string $message, int|bool $eol = 1)
 */
class Output implements OutputInterface
{
    const INFO = [Format::CYAN];
    const SUCCESS = [Format::GREEN];
    const WARNING = [Format::YELLOW];
    const ERROR = [Format::RED];
    const COMMENT = [Format::ITALIC, Format::DIM];

    protected $stream;
    protected bool $decorated;
    public readonly array $styles;

    public function __construct($stream = STDOUT, null|bool $decorated = null, array $styles = [])
    {
        $this->stream = $stream;
        $this->decorated = $decorated ?? $this->hasColorSupport();
        $this->styles = $styles;
    }

    /**
     * Write a message to output stream using predefined style or custom formatting
     */
    public function write(string $message, int|bool $eol = false, array $style = []): self
    {
        $message = $this->decorated
            ? Format::apply($message, ...$style)
            : preg_replace('/\033\[[0-9;]*m/', '', $message);

        fwrite($this->stream, $message . str_repeat(PHP_EOL, $eol));

        return $this;
    }

    public function __call(string $name, array $arguments)
    {
        if (!$style = isset($this->styles[$name]) ? $this->styles[$name] : self::{strtoupper($name)} ?? null) {
            throw new \BadMethodCallException(sprintf('Undefined style "%s".', $name));
        }

        $arguments[1] ??= true;

        return $this->write(...$arguments, style: $style);
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
    public function setDecorated(bool $decorated): self
    {
        $this->decorated = $decorated;

        return $this;
    }

    /**
     * Add custom style
     */
    public function addStyle(string $name, array $style): self
    {
        $this->styles[$name] = $style;

        return $this;
    }

    protected function hasColorSupport(): bool
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return (
                getenv('ANSICON') !== false
                || getenv('ConEmuANSI') === 'ON'
                || getenv('TERM') === 'xterm'
                || getenv('TERM') === 'xterm-256color'
            );
        }

        return function_exists('posix_isatty') && posix_isatty($this->stream);
    }
}
