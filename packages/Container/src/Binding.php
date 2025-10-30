<?php

namespace Phantasm\Container;

use Phantasm\Contracts\Container\Binding as BindingContract;

enum Binding implements BindingContract
{
    case RESOLVE;
    case SINGLETON;
    case SCOPED;
}
