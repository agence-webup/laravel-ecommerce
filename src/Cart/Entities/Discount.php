<?php

namespace Webup\Ecommerce\Cart\Entities;

use JsonSerializable;
use Webup\Ecommerce\Traits\ReadOnlyProperties;


abstract class Discount implements JsonSerializable
{
    use ReadOnlyProperties;

    protected $id;
    protected $name;
    protected $deletable;
    // application type : 1 = %, 2 = flat
    protected $discount_type;
    // discount value (percentage or flat value, depending on the previous parameter)
    protected $value;
    // scope of application : 1 = order only / 2 = order inc. shipping / 3 = product_group / 4 = specific products
    protected $discount_scope;
    protected $metadata;

    protected $errorMessage;

    protected $products_discounts = 0;
    protected $vouchers_discounts = 0;
    protected $shipping_discounts = 0;


    const DISCOUNT_TYPE_FLAT = 1;
    const DISCOUNT_TYPE_PERCENT = 2;

    const DISCOUNT_SCOPE_ORDER_ONLY = 1;
    const DISCOUNT_SCOPE_ORDER_INC_SHIPPING = 2;
    const DISCOUNT_SCOPE_PRODUCT_GROUP = 3;
    const DISCOUNT_SCOPE_PRODUCT_CUSTOM = 4;
    const DISCOUNT_SCOPE_SHIPPING_ONLY = 5;

    public function __construct($id, $name, $discount_type, $value, $discount_scope, $deletable)
    {
        $this->id = $id;
        $this->deletable = $deletable;
        $this->name = $name;
        $this->discount_type = $discount_type;
        $this->value = ($discount_type == self::DISCOUNT_TYPE_PERCENT) ? $this->cleanPercentValue($value) : $value;
        $this->discount_scope = $discount_scope;
        $this->metadata = [];
        $this->errorMessage = null;
    }

    public function setMeta($name, $value)
    {
        $this->metadata[$name] = $value;
    }

    public function getMeta($name)
    {
        if (array_key_exists($name, $this->metadata)) {
            return $this->metadata[$name];
        }
        return null;
    }

    protected function cleanPercentValue($value)
    {
        return (abs($value) > 100 ? 100 : abs($value));
    }

    // Apply the discount
    public function apply(Cart $cart, $overrideAppliedDiscounts = false, $appliedDiscounts = array())
    {
        $productsDiscounts = array();
        $totalPriceDiscounted = 0;
        $shippingPriceDiscounted = 0;
        $this->products_discounts = 0;
        $this->errorMessage = null;

        $validityResponse = $this->checkValidity($cart, $overrideAppliedDiscounts, $appliedDiscounts);

        if ($validityResponse->success) {
            $totalPrice = $cart->product_total;
            $shippingPrice = $cart->shipping->cost;

            // since it's a protected property we must copy the products_matchs attribute to another variable to get the empty() function to work
            $productMatchs = $validityResponse->products_matchs;
            if ($this->discount_scope !== self::DISCOUNT_SCOPE_SHIPPING_ONLY && !empty($productMatchs)) {
                $productsDiscounts = $this->getDiscountOnProducts($validityResponse->products_matchs);
            } else {
                switch ($this->discount_scope) {
                    case self::DISCOUNT_SCOPE_ORDER_ONLY:
                        $totalPriceDiscounted = $this->getDiscountOnPrice($totalPrice);
                        break;
                    case self::DISCOUNT_SCOPE_ORDER_INC_SHIPPING:
                        $totalPriceDiscounted = $this->getDiscountOnPrice($totalPrice + $shippingPrice);
                        break;
                    case self::DISCOUNT_SCOPE_SHIPPING_ONLY:
                        $shippingPriceDiscounted = $this->getDiscountOnPrice($shippingPrice);
                        break;
                    case self::DISCOUNT_SCOPE_PRODUCT_GROUP:
                    case self::DISCOUNT_SCOPE_PRODUCT_CUSTOM:
                        $productsDiscounts = $this->getDiscountOnProducts($validityResponse->products_matchs);
                        break;
                }
            }
        } else {
            $this->errorMessage = $validityResponse->message;
        }

        $this->vouchers_discounts = $totalPriceDiscounted;
        $this->shipping_discounts = $shippingPriceDiscounted;

        foreach ($productsDiscounts as $productId => $productDiscount) {
            foreach ($cart->products as $key => $cartProduct) {
                if ($cartProduct->product_id == $productId) {
                    $cart->products[$key]->addDiscount($this, $productDiscount);
                    $this->products_discounts += $productDiscount;
                }
            }
        }
    }

    // returns the discounted part (minus X)
    public function getDiscountOnPrice($price)
    {
        $discount = 0;
        switch ($this->discount_type) {
            case self::DISCOUNT_TYPE_FLAT: // retrieve a flat value
                if (($this->value) > $price) { // discount can't be more than the total amount
                    $discount = $price;
                } else {
                    $discount = $this->value;
                }
                break;
            case self::DISCOUNT_TYPE_PERCENT: // retrieve a percent value (20/30/40%)
                $discount = $price * (round($this->value / 100, 2));
                break;
            default:
                break;
        }
        return round($discount, 2);
    }

    // returns the price
    public function applyDiscountOnPrice($price)
    {
        switch ($this->discount_type) {
            case self::DISCOUNT_TYPE_FLAT: // retrieve a flat value
                $price -= $this->value;
                break;
            case self::DISCOUNT_TYPE_PERCENT: // retrieve a percent value (20/30/40%)
                $price -= $price * (round($this->value / 100));
                break;
            default:
                break;
        }
        return round($price, 2);
    }

    protected function getDiscountOnProducts($productsMatchs) {
        $appliedDiscount = 0;
        $productsDiscounts = [];
        foreach ($productsMatchs as $productMatch) {
            // for a percent discount, repeat on each matched products
            if ($this->discount_type == self::DISCOUNT_TYPE_PERCENT) {
                // only apply on base price, and not discounted price by any means, (discount_price from db or voucher usage)
                $discountOnPrice = $this->getDiscountOnPrice($productMatch->total_price);
                $productsDiscounts[$productMatch->product_id] = $discountOnPrice;
                // $totalPriceDiscounted += $discountOnPrice;
            } else if ($this->discount_type == self::DISCOUNT_TYPE_FLAT) {
                // for a flat discount, apply it on each matched product until we reach the desired amount
                $discountOnPrice = $this->getDiscountOnPrice($productMatch->total_price);
                $appliedDiscount += $discountOnPrice;

                // if the total applied discount exceed the voucher flat value, cap it to voucher flat value
                if ($appliedDiscount > $this->value) {
                    // Example :
                    // voucher value = 50€
                    // ex-appliedDiscount = 40€ (already deducted on the previous products)
                    // discountOnPrice = 30€ (for the current product)
                    // current appliedDiscount = 70€ (40 + 30), which is more than voucher value, we will need to cap discountOnPrice
                    // new discountOnPrice = voucher value - (appliedDiscount - discountOnPrice) 50 - (70 - 30) = 10€
                    // appliedDiscount will now be 50€ (previously 40€ + 10€ now)
                    $discountOnPrice = $this->value - ($appliedDiscount - $discountOnPrice);
                    $appliedDiscount = $this->value;
                }
                $productsDiscounts[$productMatch->product_id] = $discountOnPrice;
            }
        }
        return $productsDiscounts;
    }

    abstract public function checkValidity(Cart $cart, $overrideAppliedDiscounts, $appliedDiscounts): CheckDiscountValidityResponse;

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
