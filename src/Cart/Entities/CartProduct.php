<?php

namespace Webup\Ecommerce\Cart\Entities;

use JsonSerializable;
use Webup\Ecommerce\Traits\ReadOnlyProperties;

class CartProduct implements JsonSerializable
{
    use ReadOnlyProperties;

    protected $productId;
    protected $name;
    protected $price;
    protected $quantity;
    protected $metadata;


    public function __construct(array $data)
    {
        $this->productId = $data["productId"];
        $this->name = $data["name"];
        $this->price = $data["price"];
        $this->metadata = array_get($data, "metadata", []);
    }

    public function setQuantity(int $quantity)
    {
        $this->quantity = $quantity;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
