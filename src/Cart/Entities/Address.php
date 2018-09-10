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
    protected $email;
    protected $phone;
    protected $address1;
    protected $address2;
    protected $postcode;
    protected $city;
    protected $country;

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
