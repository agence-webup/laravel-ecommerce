<?php

namespace Webup\Ecommerce\Cart\Services;

use Illuminate\Support\Str;
use Webup\Ecommerce\Cart\Entities\Address;
use Webup\Ecommerce\Cart\Entities\Cart;
use Webup\Ecommerce\Cart\Entities\Product;
use Webup\Ecommerce\Cart\Repositories\ProductRepository;
use Webup\Ecommerce\Cart\Repositories\CartRepository;

class CartService
{
    protected $productRepository;
    protected $cartRepository;

    public function __construct(ProductRepository $productRepository, CartRepository $cartRepository)
    {
        $this->productRepository = $productRepository;
        $this->cartRepository = $cartRepository;
    }

    public function createCart() : Cart
    {
        $cart = new Cart((string)Str::uuid());
        $this->cartRepository->save($cart);

        return $cart;
    }

    public function getCart(string $cartId) : ? Cart
    {
        return $this->cartRepository->getById($cartId);
    }

    public function setProduct($cartId, Product $Product, int $quantity)
    {
        $cart = $this->cartRepository->getById($cartId);
        if (!$cart) {
            throw new \Exception("Cart not found", 1);
        }

        if ($quantity === 0) {
            $cart->removeProduct($Product);
        } else {
            $Product->setQuantity($quantity);
            // TODO: Check stock
            $cart->putProduct($Product);
        }

        $this->update($cart);

        $this->cartRepository->save($cart);

        return $cart;
    }

    public function setDeliveryAddress(Cart $cart, Address $address)
    {
        $cart->setDeliveryAddress($address);
        $this->update($cart);

        return $cart;
    }

    public function setInvoiceAddress(Cart $cart, Address $address)
    {
        $cart->setInvoiceAddress($address);
        $this->update($cart);

        return $cart;
    }

    public function addCoupon(Cart $cart, $couponCode)
    {
        $coupon = $this->couponRepository->getByCode($couponCode);
        if (!$coupon) {
            throw new \Exception("Coupon not found", 1);
        }

        if ($coupon->expirattionDate < now()) {
            throw new \Exception("Coupon has expired", 1);
        }

        // TODO: Check rule
        // vous pouvez utilisé qu'un code par commonde
        // montant mini du panier
        // ce code a expiré
        // ce code ne vous appartient pas (avoir)

        $cart->addDiscount($coupon);
        $this->update($cart);

        return $cart;
    }

    protected function update(Cart $cart)
    {
        $cart->update();
        //$cart->update($shippingService,$discountRuleRepository);
    }
}
