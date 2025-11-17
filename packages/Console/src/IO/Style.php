<?php

namespace Phantasm\Console\IO;

class Style
{
    public function __construct(
        protected ?Color $foreground = null,
        protected ?Color $background = null,
        protected array $options = []
    ) {}

    public function format(Format $format): self
    {
        $this->options[] = $format;
        return $this;
    }

    public function apply(string $text): string
    {
        $codes = [];

        if ($this->foreground) {
            $codes[] = $this->foreground->foreground();
        }

        if ($this->background) {
            $codes[] = $this->background->background();
        }

        foreach ($this->options as $option) {
            $codes[] = $option->value;
        }

        return empty($codes)
            ? $text
            : sprintf("\033[%sm%s\033[0m", implode(';', $codes), $text);
    }
}
