<?php

namespace Webup\Ecommerce\Cart\Entities;

use JsonSerializable;
use Webup\Ecommerce\Traits\ReadOnlyProperties;

class Product implements JsonSerializable
{
    use ReadOnlyProperties;

    protected $product_id;
    protected $name;
    protected $price;
    protected $quantity;
    protected $metadata;

    public function __construct(array $data)
    {
        $this->product_id = $data["product_id"];
        $this->name = $data["name"];
        $this->price = $data["price"];
        $this->metadata = array_get($data, "metadata", []);
    }

    public function setQuantity(int $quantity)
    {
        $this->quantity = $quantity;
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
