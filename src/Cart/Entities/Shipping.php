<?php

namespace Webup\Ecommerce\Cart\Entities;

use Webup\Ecommerce\Traits\ReadOnlyProperties;

class Shipping
{
    use ReadOnlyProperties;

    protected $cost;
    protected $carrier;
    protected $metadata;

    public static function createFromArray(array $data)
    {
        $shipping = new Shipping();
        $shipping->cost = array_get($data, "cost", null);
        $shipping->carrier = array_get($data, "carrier", null);
        $shipping->metadata = array_get($data, "metadata", []);

        return $shipping;
    }

    public function getMeta($name)
    {
        if (array_key_exists($name, $this->metadata)) {
            return $this->metadata[$name];
        }
        return null;
    }
}
