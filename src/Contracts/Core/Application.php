<?php

namespace WeStacks\Framework\Contracts\Core;

interface Application
{
    public function http();
    public function console();
    public static function make(string $root);
}
