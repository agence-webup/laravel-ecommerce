<?php

namespace Webup\Ecommerce\Cart\Entities;

use JsonSerializable;
use Webup\Ecommerce\Traits\ReadOnlyProperties;

class CheckDiscountValidityResponse implements JsonSerializable
{
    use ReadOnlyProperties;

    protected $id;
    protected $company;

    protected $success;
    protected $discount;
    protected $products_matchs;
    protected $message;
    protected $code;

    public function __construct($success, $message, $code, $discount, $products_matchs = [])
    {
        $this->success = $success;
        $this->message = $message;
        $this->code = $code;
        $this->discount = $discount;
        $this->products_matchs = $products_matchs;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
