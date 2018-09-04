<?php

namespace Webup\Ecommerce\Cart\Entities;

use JsonSerializable;
use Webup\Ecommerce\Traits\ReadOnlyProperties;


class CartDiscount implements JsonSerializable
{
    use ReadOnlyProperties;

    protected $name;
    protected $valueType;
    protected $value;

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
