<?php

namespace Webup\Ecommerce\Values;

use Webup\Ecommerce\Traits\ReadOnlyProperties;
use JsonSerializable;

/**
 * Price
 * @property-read float $price Price excluding tax
 * @property-read float $tax Tax amount
 * @property-read float $taxedPrice Price including tax
 */
class Price implements JsonSerializable
{
    use ReadOnlyProperties;

    protected $price = 0;
    protected $tax = 0;

    public function __construct($price = 0, $tax = 0)
    {
        $this->price = $price;
        $this->tax = $tax;
    }

    public static function createWithRate($price, $rate)
    {
        return new static($price, $price * $rate);
    }

    public static function createFromTTCWithRate($price, $rate)
    {
        $priceHt = (100 * $price) / (100 + ($rate * 100));

        return new static($priceHt, $price - $priceHt);
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getTax()
    {
        return $this->tax;
    }

    public function getTaxedPrice()
    {
        return $this->price + $this->tax;
    }

    public function add(Price $price)
    {
        $this->price += $price->price;
        $this->tax += $price->tax;

        return $this;
    }

    public function multiply($quantity)
    {
        $this->price *= $quantity;
        $this->tax *= $quantity;

        return $this;
    }

    public function copy()
    {
        return clone $this;
    }

    /**
     * Convert the object into something JSON serializable.
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'price' => $this->price,
            'tax' => $this->tax,
            'taxedPrice' => $this->taxedPrice,
        ];
    }
}
