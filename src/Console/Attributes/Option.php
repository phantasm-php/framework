<?php

namespace Phantasm\Console\Attributes;

#[\Attribute]
class Option
{
    public function __construct(
        public string $description = '',
        public array $shortcuts = [],
    ) {}
}
