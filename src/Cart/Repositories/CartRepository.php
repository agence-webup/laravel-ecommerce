<?php

namespace Webup\Ecommerce\Cart\Repositories;

use Webup\Ecommerce\Cart\Entities\Cart;

interface CartRepository
{
    public function getById(string $cartId) : ? Cart;

    public function save(Cart $cart) : void;

    public function destroyById(string $cartId) : void;
}
