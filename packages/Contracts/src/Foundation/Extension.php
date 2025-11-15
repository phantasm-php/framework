<?php

namespace Phantasm\Contracts\Foundation;

use Phantasm\Contracts\Container\Container;

interface Extension
{
    /**
     * Installs extension into framework. Should return post-install callback, which runs after all extensions are installed.
     */
    public static function install(Container $container, \Reflector $reflection, ?Extension $context): ?callable;
}
