<?php

namespace Webup\Ecommerce\Cart\Entities;

use JsonSerializable;
use Webup\Ecommerce\Traits\ReadOnlyProperties;


class Customer implements JsonSerializable
{
    use ReadOnlyProperties;

    protected $id;
    protected $firstname;
    protected $lastname;
    protected $email;

    public static function createFromArray(array $data)
    {
        $customer = new Customer();
        $customer->id = array_get($data, "id", null);
        $customer->firstname = array_get($data, "firstname", null);
        $customer->lastname = array_get($data, "lastname", null);
        $customer->email = array_get($data, "email", null);
        $customer->metadata = array_get($data, "metadata", []);
        return $customer;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
