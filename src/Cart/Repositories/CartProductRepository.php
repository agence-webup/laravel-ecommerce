<?php

namespace Webup\Ecommerce\Cart\Repositories;

use Webup\Ecommerce\Cart\Entities\Product;

interface CartProductRepository
{
    public function getById(string $productId) : ? Product;
}
