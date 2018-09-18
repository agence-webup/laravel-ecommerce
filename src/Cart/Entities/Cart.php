<?php

namespace Webup\Ecommerce\Cart\Entities;

use JsonSerializable;
use Webup\Ecommerce\Traits\ReadOnlyProperties;
use Webup\Ecommerce\Cart\Entities\Address;
use Webup\Ecommerce\Cart\Entities\Shipping;
use Webup\Ecommerce\Cart\Entities\Product;
use Webup\Ecommerce\Cart\Entities\Customer;

class Cart implements JsonSerializable
{
    use ReadOnlyProperties;

    protected $uuid;
    protected $customer;
    protected $delivery_address;
    protected $invoice_address;
    protected $products;
    protected $product_count;
    protected $product_total;
    protected $discounts;
    protected $discount_total;
    protected $shipping;
    protected $total_ht;
    protected $tax;
    protected $total;
    protected $metadata;

    public function __construct(string $uuid)
    {
        $this->uuid = $uuid;
        $this->customer = null;
        $this->delivery_address = null;
        $this->invoice_address = null;
        $this->products = [];
        $this->product_count = 0;
        $this->product_total = 0;
        $this->discounts = [];
        $this->discount_total = 0;
        $this->shipping = new Shipping();
        $this->total_ht = 0;
        $this->tax = 0;
        $this->total = 0;
    }

    public function putProduct(Product $product)
    {
        $found = false;
        foreach ($this->products as $key => $p) {
            if ($p->product_id == $product->product_id) {
                $this->products[$key] = $product;
                $found = true;
            }
        }

        if (!$found) {
            $this->products[] = $product;
        }

    }

    public function removeProduct(Product $product)
    {
        if (array_key_exists($product->product_id, $this->products)) {
            unset($this->products[$product->product_id]);
        }
    }

    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function setShipping(Shipping $shipping)
    {
        $this->shipping = $shipping;
    }

    public function setDeliveryAddress(Address $address)
    {
        $this->delivery_address = $address;
    }

    public function setInvoiceAddress(Address $address)
    {
        $this->invoice_address = $address;
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
        $product_count = 0;
        $product_total = 0;
        foreach ($this->products as $product) {
            $product_count += $product->quantity;
            $product_total += $product->price * $product->quantity;
        }
        $this->product_count = $product_count;
        $this->product_total = $product_total;

        // Réduction selon des régles ex: 2 achetés 3e offert
        // $discountRules = $this->discountRuleRepository->all();
        // foreach ($discountRules as $discountRule) {
        //     $discountRule->apply($this);
        // }

        $discount_total = 0;
        foreach ($this->discounts as $discount) {
            $discount_total += $discount->amount;
        }
        $this->discount_total = $discount_total;

        // Frais de port selon l'addresse
        // $this->shipping = $this->shipping->calculate($this);
        $this->total = $this->product_total + $this->discount_total + $this->shipping->cost;
        // taxe selon le pays
        // $this->tax = $this->taxService->calculate($this);
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
