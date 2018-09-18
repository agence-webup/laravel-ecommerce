<?php

namespace Webup\Ecommerce\Cart\Entities;

use JsonSerializable;
use Webup\Ecommerce\Traits\ReadOnlyProperties;


class Discount implements JsonSerializable
{
    use ReadOnlyProperties;

    protected $name;
    protected $value_type;
    protected $value;

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
