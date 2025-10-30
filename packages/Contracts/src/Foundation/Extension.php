<?php

namespace Phantasm\Contracts\Foundation;

use Phantasm\Contracts\Container\Container;

interface Extension
{
    public static function register(Container $container, \Reflector $reflection, ?Extension $context): void;
    public static function boot(Container $container, \Reflector $reflection, ?Extension $context): void;
}
