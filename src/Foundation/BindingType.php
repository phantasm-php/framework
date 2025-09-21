<?php

namespace WeStacks\Framework\Foundation;

use WeStacks\Framework\Contracts\Foundation\BindingType as BindingTypeContract;

enum BindingType implements BindingTypeContract
{
    case BIND;
    case SINGLETON;
    case SCOPED;
}
