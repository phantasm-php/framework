<?php

namespace Phantasm\Contracts\Foundation;

use Phantasm\Contracts\Container\Container;

interface Provider
{
    public static function register(Container $container, \Reflector $reflection, ?Provider $context): void;
    public static function boot(Container $container, \Reflector $reflection, ?Provider $context): void;
}
