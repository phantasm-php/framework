<?php

namespace Phantasm\Contracts\Console\IO;

interface Input
{
    public function argument(int $index, mixed $default = null): mixed;
    public function option(string $name, mixed $default = null): mixed;
    public function hasOption(string $name): bool;
}
