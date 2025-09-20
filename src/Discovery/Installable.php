<?php

namespace WeStacks\Framework\Discovery;

use WeStacks\Framework\Contracts\Container\Container;

interface Installable
{
    /**
     * Install the discovered service into the container.
     */
    public function install(Container $container): void;
}
