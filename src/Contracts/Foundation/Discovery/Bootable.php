<?php

namespace Phantasm\Contracts\Foundation\Discovery;

use Phantasm\Contracts\Foundation\Application;

interface Bootable
{
    /**
     * Configure the discovered service after the application is booted.
     */
    public static function boot(Application $app, \Reflector $reflection, $context = null): void;
}
