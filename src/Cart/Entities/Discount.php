<?php

namespace Webup\Ecommerce\Cart\Entities;

use JsonSerializable;
use Webup\Ecommerce\Traits\ReadOnlyProperties;


class Discount implements JsonSerializable
{
    use ReadOnlyProperties;

    protected $name;
    // type : 1 = discount, 2 = voucher
    protected $type;
    // application type : 1 = %, 2 = flat
    protected $discount_type;
    // discount value (percentage or flat value, depending on the previous parameter)
    protected $value;
    // scope of application : 1 = order only / 2 = order inc. shipping / 3 = product_group / 4 = specific products
    protected $products_scope;
    // products (declinaisons) ids concerned by the scope (faculative, depending on the products_scope)
    protected $products_matchs_ids;
    // products (declinaisons) concerned by the scope (faculative, depending on the products_scope)
    protected $products_matchs;

    const TYPE_DISCOUNT = 1;
    const TYPE_VOUCHER = 2;

    // could also use App\Values\VoucherDiscountType
    const DISCOUNT_TYPE_FLAT = 1;
    const DISCOUNT_TYPE_PERCENT = 2;

    // could also use App\Values\VoucherProductScope
    const PRODUCT_SCOPE_ORDER_ONLY = 1;
    const PRODUCT_SCOPE_ORDER_INC_SHIPPING = 2;
    const PRODUCT_SCOPE_PRODUCT_GROUP = 3;
    const PRODUCT_SCOPE_PRODUCT_CUSTOM = 4;
    const PRODUCT_SCOPE_SHIPPING_ONLY = 5;

    public function __construct($name, $type, $discount_type, $value, $products_scope, $products_matchs = array())
    {
        $this->name = $name;
        $this->type = $type;
        $this->discount_type = $discount_type;
        $this->value = $this->cleanPercentValue($value);
        $this->products_scope = $products_scope;
        $this->products_matchs = $products_matchs;
    }

    protected function cleanPercentValue($value)
    {
        return (abs($value) > 100 ? 100 : abs($value));
    }

    // Apply the discount 
    public function getDiscountApplication($totalPrice, $shippingPrice)
    {
        $productsDiscounts = array();
        $totalPriceDiscounted = 0;
        $shippingPriceDiscounted = 0;
        switch ($this->products_scope) {
            case self::PRODUCT_SCOPE_ORDER_ONLY:
                $totalPriceDiscounted = $this->getDiscountOnPrice($totalPrice);
                break;
            case self::PRODUCT_SCOPE_ORDER_INC_SHIPPING:
                $totalPriceDiscounted = $this->getDiscountOnPrice($totalPrice + $shippingPrice);
                break;
            case self::PRODUCT_SCOPE_SHIPPING_ONLY:
                $shippingPriceDiscounted = $this->getDiscountOnPrice($shippingPrice);
                break;
            case self::PRODUCT_SCOPE_PRODUCT_GROUP:
            case self::PRODUCT_SCOPE_PRODUCT_CUSTOM:
                foreach ($this->products_matchs as $productMatch) {
                    // only apply on base price, and not discounted price by any means, (discount_price from db or voucher usage)
                    $discountOnPrice = $this->getDiscountOnPrice($productMatch->price);
                    $productsDiscounts[$productMatch->product_id] = $discountOnPrice;
                    // $totalPriceDiscounted += $discountOnPrice;
                }
                break;
        }

        return array(
            'total' => $totalPriceDiscounted,
            'shipping' => $shippingPriceDiscounted,
            'products' => $productsDiscounts,
        );
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

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
