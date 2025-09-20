<?php

namespace WeStacks\Framework\Discovery;

use Reflector;

abstract class Discoverable
{
    protected $source;

    abstract public function setSource(Reflector $source): void;
}
