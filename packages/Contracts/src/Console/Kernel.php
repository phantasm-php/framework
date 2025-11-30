<?php

namespace Phantasm\Contracts\Console;

use Phantasm\Contracts\Console\IO\Input;
use Phantasm\Contracts\Console\IO\Output;

interface Kernel
{
    public function handle(?Input $input = null, ?Output $output = null): int;
}
