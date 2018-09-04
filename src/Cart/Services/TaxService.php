<?php

namespace Webup\Ecommerce\Cart\Services;

use Webup\Ecommerce\Cart\Entities\Cart;

class TaxService
{
    public function calculate(Cart $cart)
    {
        return $cart->total * 0.2;
    }
}
