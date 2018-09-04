<?php

namespace Webup\Ecommerce\Traits;

trait ReadOnlyProperties
{
    public function __get($key)
    {
        if (property_exists($this, $key)) {
            return $this->{$key};
        }
        throw new \Exception("Unknown property $key in Cart object", 1);
    }

}