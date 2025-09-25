<?php

namespace Phantasm\Foundation;


if (! function_exists(__NAMESPACE__.'\app')) {
    /**
     * @template T
     * @param class-string<T>|null $abstract
     * @return T|Application
     */
    function app(?string $abstract = null)
    {
        $app = Application::instance();

        return $abstract ? $app->get($abstract) : $app;
    }
}

if (! function_exists(__NAMESPACE__.'\env')) {
    function env(string $key, $default = null): mixed
    {
        return getenv($key) ?: $default;
    }
}
