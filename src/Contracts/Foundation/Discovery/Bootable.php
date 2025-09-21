<?php

namespace WeStacks\Framework\Contracts\Foundation\Discovery;

use WeStacks\Framework\Contracts\Foundation\Application;

interface Bootable
{
    /**
     * Configure the discovered service after the application is booted.
     */
    public static function boot(Application $app, \Reflector $reflection, $context = null): void;
}
