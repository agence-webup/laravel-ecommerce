<?php

namespace Webup\Ecommerce\Cart\Services;

use Webup\Ecommerce\Cart\Entities\Cart;

interface ShippingServiceInterface
{
    public function getShippingCost(Cart $cart);
}
