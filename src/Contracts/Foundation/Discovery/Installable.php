<?php

namespace Phantasm\Contracts\Foundation\Discovery;

use Phantasm\Contracts\Foundation\Application;

interface Installable
{
    /**
     * Install the discovered service into the application.
     */
    public static function install(Application $app, \Reflector $reflection, $context = null): void;
}
