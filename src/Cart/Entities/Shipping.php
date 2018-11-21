<?php

namespace Webup\Ecommerce\Cart\Entities;

use Webup\Ecommerce\Traits\ReadOnlyProperties;

class Shipping
{
    use ReadOnlyProperties;

    protected $cost;
    protected $carrier;


    public static function createFromArray(array $data)
    {
        $shipping = new Shipping();
        $shipping->cost = array_get($data, "cost", null);
        $shipping->carrier = array_get($data, "carrier", null);
        return $shipping;
    }
}
