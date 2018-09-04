<?php

namespace Webup\Ecommerce\Cart\Entities;

use JsonSerializable;
use Webup\Ecommerce\Traits\ReadOnlyProperties;


class Address implements JsonSerializable
{
    use ReadOnlyProperties;

    protected $company;
    protected $firstname;
    protected $lastname;
    protected $address1;
    protected $address2;
    protected $postcode;
    protected $city;
    protected $phone;
    protected $country;

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
