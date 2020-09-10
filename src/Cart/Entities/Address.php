<?php

namespace Webup\Ecommerce\Cart\Entities;

use JsonSerializable;
use Webup\Ecommerce\Traits\ReadOnlyProperties;

class Address implements JsonSerializable
{
    use ReadOnlyProperties;

    protected $id;
    protected $company;
    protected $firstname;
    protected $lastname;
    protected $email;
    protected $phone;
    protected $address1;
    protected $address2;
    protected $floor;
    protected $elevator;
    protected $postcode;
    protected $city;
    protected $country;
    protected $metadata;

    public static function createFromArray(array $data)
    {
        $address = new Address();

        $address->id = array_get($data, "id", null);
        $address->company = array_get($data, "company", null);
        $address->firstname = array_get($data, "firstname", null);
        $address->lastname = array_get($data, "lastname", null);
        $address->email = array_get($data, "email", null);
        $address->phone = array_get($data, "phone", null);
        $address->address1 = array_get($data, "address1", null);
        $address->address2 = array_get($data, "address2", null);
        $address->floor = array_get($data, "floor", null);
        $address->elevator = array_get($data, "elevator", null);
        $address->postcode = array_get($data, "postcode", null);
        $address->city = array_get($data, "city", null);
        $address->country = array_get($data, "country", null);
        $address->metadata = array_get($data, "metadata", []);

        return $address;
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
