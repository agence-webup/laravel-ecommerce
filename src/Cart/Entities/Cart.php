<?php

namespace Webup\Ecommerce\Cart\Entities;

use JsonSerializable;
use Webup\Ecommerce\Traits\ReadOnlyProperties;
use Webup\Ecommerce\Cart\Entities\Address;
use Webup\Ecommerce\Cart\Entities\Shipping;
use Webup\Ecommerce\Cart\Entities\CartProduct;

class Cart implements JsonSerializable
{
    use ReadOnlyProperties;

    protected $uuid;
    protected $deliveryAddress;
    protected $invoiceAddress;
    protected $products;
    protected $productCount;
    protected $productTotal;
    protected $discounts;
    protected $shipping;
    protected $total;
    protected $tax;

    public function __construct(string $uuid)
    {
        $this->uuid = $uuid;
        $this->deliveryAddress = new Address();
        $this->invoiceAddress = new Address();
        $this->products = [];
        $this->productCount = 0;
        $this->productTotal = 0;
        $this->discounts = [];
        $this->shipping = new Shipping();
        $this->total = 0;
        $this->tax = 0;
    }

    public function putProduct(CartProduct $product)
    {
        $this->products[$product->productId] = $product;
    }

    public function removeProduct(CartProduct $product)
    {
        if (array_key_exists($product->productId, $this->products)) {
            unset($this->products[$product->productId]);
        }
    }

    public function update()
    {
        $productCount = 0;
        $productTotal = 0;
        foreach ($this->products as $product) {
            $productCount += $product->quantity;
            $productTotal += $product->price * $product->quantity;
        }
        $this->productCount = $productCount;
        $this->productTotal = $productTotal;

        // Réduction selon des régles ex: 2 achetés 3e offert
        // $discountRules = $this->discountRuleRepository->all();
        // foreach ($discountRules as $discountRule) {
        //     $discountRule->apply($this);
        // }

        $discount = 0;
        foreach ($this->discounts as $discount) {
            $discount += $discount->amount;
        }

        // Frais de port selon l'addresse
        // $this->shipping = $this->shipping->calculate($this);
        $this->total = $productTotal + $discount + $this->shipping->cost;
        // taxe selon le pays
        // $this->tax = $this->taxService->calculate($this);
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
