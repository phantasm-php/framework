<?php

namespace Phantasm\Contracts\Console\IO;

interface Output
{
    public function write(string $message, int|bool $eol = false, array $style = []): self;
}
