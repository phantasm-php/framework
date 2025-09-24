<?php

namespace WeStacks\Framework\Console\Attributes;

#[\Attribute]
class Argument
{
    public function __construct(
        public string $description,
    ) {}
}
