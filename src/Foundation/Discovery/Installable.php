<?php

namespace WeStacks\Framework\Foundation\Discovery;

use WeStacks\Framework\Contracts\Foundation\Application;

interface Installable
{
    /**
     * Install the discovered service into the application.
     */
    public function install(Application $app): void;
}
