<?php

namespace Webup\Ecommerce\Cart\Entities;

use Webup\Ecommerce\Traits\ReadOnlyProperties;

class Shipping
{
    use ReadOnlyProperties;

    protected $cost;
    protected $carrier;
}
