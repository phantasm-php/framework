<?php

namespace Phantasm\Foundation;

use Phantasm\Contracts\Foundation\BindingType as BindingTypeContract;

enum BindingType implements BindingTypeContract
{
    case BIND;
    case SINGLETON;
    case SCOPED;
}
