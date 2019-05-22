<?php

namespace Webup\Ecommerce\Cart\Entities;

use JsonSerializable;
use Webup\Ecommerce\Traits\ReadOnlyProperties;
use Webup\Ecommerce\Values\Price;

class Product implements JsonSerializable
{
    use ReadOnlyProperties;

    protected $product_id;
    protected $name;
    protected $price;
    protected $total_price;
    protected $discount_price;
    protected $total_discount_price;
    protected $discount_label;
    protected $quantity;
    protected $discounts;
    protected $metadata;

    public function __construct(array $data)
    {
        $this->product_id = $data["product_id"];
        $this->name = $data["name"];
        $this->price = $data["price"];
        $this->total_price = new Price(0, 0);
        $this->discount_price = $data["discount_price"];
        $this->discount_label = $data["discount_label"];
        $this->discounts = [];
        $this->metadata = array_get($data, "metadata", []);
    }

    public function setQuantity(int $quantity)
    {
        $this->quantity = $quantity;
        $this->total_price = $this->price->copy()->multiply($quantity);
    }

    public function addDiscount(Discount $discount, $discountPrice)
    {
        $price = $this->total_price;
        if ($this->discount_price) {
            $price = $this->discount_price;
        }
        $this->total_discount_price = $price - $discountPrice;

        $this->discounts[] = $discount;
    }

    public function clearDiscounts()
    {
        $this->discounts = [];
    }

    public function getMeta($name)
    {
        if (array_key_exists($name, $this->metadata)) {
            return $this->metadata[$name];
        }

        return null;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
