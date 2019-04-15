<?php

namespace Webup\Ecommerce\Cart\Services;

use Webup\Ecommerce\Cart\Entities\Cart;

interface ShippingServiceInterface
{
    /**
     * Called 2 times : before discount being applied and after discount applied
     */
    public function getShippingCost(Cart $cart);
}
