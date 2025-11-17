<?php

namespace Phantasm\Console\IO;

class Theme
{
    public function __construct(
        protected array $styles = []
    ) {
        $this->styles = array_merge([
            'info' => new Style(Color::CYAN),
            'success' => new Style(Color::GREEN),
            'warning' => new Style(Color::YELLOW),
            'error' => new Style(Color::RED, background: Color::BLACK),
            'comment' => new Style(Color::GRAY),
            'question' => new Style(Color::BLUE),
            'highlight' => new Style(Color::WHITE, background: Color::BLUE),
        ], $styles);
    }

    /**
     * Apply a style to text
     */
    public function apply(string $name, string $text): string
    {
        if (!isset($this->styles[$name])) {
            return $text;
        }

        return $this->styles[$name]->apply($text);
    }

    /**
     * Register a custom style
     */
    public function addStyle(string $name, Style $style): void
    {
        $this->styles[$name] = $style;
    }
}
