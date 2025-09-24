<?php

namespace Phantasm\Console\Attributes;

#[\Attribute]
class Argument
{
    public function __construct(
        public string $description,
    ) {}
}
