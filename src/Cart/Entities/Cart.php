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
    protected $discountTotal;
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
        $this->discountTotal = 0;
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

    public function clearProducts()
    {
        $this->products = [];
    }

    public function clearDiscounts()
    {
        $this->discounts = [];
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

        $discountTotal = 0;
        foreach ($this->discounts as $discount) {
            $discountTotal += $discount->amount;
        }
        $this->discountTotal = $discountTotal;

        // Frais de port selon l'addresse
        // $this->shipping = $this->shipping->calculate($this);
        $this->total = $this->productTotal + $this->discountTotal + $this->shipping->cost;
        // taxe selon le pays
        // $this->tax = $this->taxService->calculate($this);
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
