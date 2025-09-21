<?php

namespace WeStacks\Framework\Contracts\Foundation\Discovery;

use WeStacks\Framework\Contracts\Foundation\Application;

interface Installable
{
    /**
     * Install the discovered service into the application.
     */
    public static function install(Application $app, \Reflector $reflection, $context = null): void;
}
