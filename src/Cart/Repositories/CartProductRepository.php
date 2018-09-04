<?php

namespace Webup\Ecommerce\Cart\Repositories;

use Webup\Ecommerce\Cart\Entities\CartProduct;

interface CartProductRepository
{
    public function getById(string $productId) : ? CartProduct;
}
