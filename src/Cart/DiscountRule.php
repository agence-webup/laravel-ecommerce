<?php

namespace Webup\Ecommerce\Cart;


class DiscountRule
{
    // Exemple de regle pour 2 produits achetés le 3 offert
    public function apply($cart)
    {
        foreach ($cart->product as $product) {
            if ($product->quantity > 3) {
                $cart->addReduction('3 produits offert', $product->price);
            }
        }
    }
}
