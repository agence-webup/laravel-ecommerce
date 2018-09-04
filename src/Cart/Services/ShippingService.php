<?php

namespace Webup\Ecommerce\Cart\Services;

use Webup\Ecommerce\Cart\Entities\Cart;

class ShippingService
{
    public function getShippingCost(Cart $cart)
    {
        if ($cart->total < 50) {
            return 0;
        }

        return 0;
    }
}
