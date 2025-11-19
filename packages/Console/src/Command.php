<?php

namespace Phantasm\Console;

abstract class Command
{
    public function __construct(
        protected string $name,
        protected string $description = '',
    ) {}
}
