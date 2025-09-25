<?php

namespace Phantasm\Contracts\Console;

interface IO
{
    public function write(string $message): void;

    public function info(string $message): void;

    public function error(string $message): void;

    public function warning(string $message): void;
}
