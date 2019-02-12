<?php

namespace Webup\Ecommerce\Cart\Entities;

use JsonSerializable;
use Webup\Ecommerce\Traits\ReadOnlyProperties;
use Webup\Ecommerce\Cart\Entities\Address;
use Webup\Ecommerce\Cart\Entities\Shipping;
use Webup\Ecommerce\Cart\Entities\Product;
use Webup\Ecommerce\Cart\Entities\Customer;

class Cart implements JsonSerializable
{
    use ReadOnlyProperties;

    protected $uuid;
    protected $customer;
    protected $delivery_address;
    protected $invoice_address;
    protected $products;
    protected $product_count;
    protected $product_total;
    protected $product_prices;
    protected $product_vouchers;
    protected $discounts;
    protected $discount_total;
    protected $products_discounts_total;
    protected $vouchers_discounts_total;
    protected $shipping_discounts_total;
    protected $shipping;
    protected $total_ht;
    protected $tax;
    protected $total;
    protected $metadata;

    public function __construct(string $uuid)
    {
        $this->uuid = $uuid;
        $this->customer = null;
        $this->delivery_address = null;
        $this->invoice_address = null;
        $this->products = [];
        $this->product_count = 0;
        $this->product_total = 0;
        $this->product_prices = [];
        $this->discounts = array(
            'discounts' => array(),
            'vouchers' => array(),
        );
        $this->discount_total = 0;
        $this->products_discounts_total = 0;
        $this->vouchers_discounts_total = 0;
        $this->shipping_discounts_total = 0;
        $this->shipping = new Shipping();
        $this->total_ht = 0;
        $this->tax = 0;
        $this->total = 0;
        $this->metadata = [];
    }

    public function putProduct(Product $product)
    {
        $found = false;
        foreach ($this->products as $key => $p) {
            if ($p->product_id == $product->product_id) {
                $this->products[$key] = $product;
                $found = true;
            }
        }

        if (!$found) {
            $this->products[] = $product;
        }

    }

    public function removeProduct(Product $product)
    {
        foreach ($this->products as $key => $cartProduct) {
            if ($cartProduct->product_id == $product->product_id) {
                unset($this->products[$key]);
                break;
            }
        }
    }

    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function setShipping(Shipping $shipping)
    {
        $this->shipping = $shipping;
    }

    public function setDeliveryAddress(Address $address)
    {
        $this->delivery_address = $address;
    }

    public function setInvoiceAddress(Address $address)
    {
        $this->invoice_address = $address;
    }

    public function clearProducts()
    {
        $this->products = [];
    }

    public function clearDiscounts()
    {
        $this->product_prices = [];
        $this->product_vouchers = [];
        $this->discounts = [];
    }

    public function setMeta($name, $value)
    {
        $this->metadata[$name] = $value;
    }

    public function addVoucher($voucher, $productsMatchs = array())
    {
        $discount = new Discount($voucher->code, Discount::TYPE_VOUCHER, $voucher->discount_type, $voucher->value, $voucher->products_scope, $productsMatchs);
        $this->addDiscount($discount);
    }

    public function addDiscount(Discount $discount)
    {
        switch ($discount->type) {
            case Discount::TYPE_VOUCHER:
                $this->discounts['vouchers'][] = $discount;
                break;
            case Discount::TYPE_DISCOUNT:
                $this->discounts['discounts'][] = $discount;
                break;
            default:
                break;
        }
        // $this->discounts[] = $discount;
    }

    public function getMeta($name)
    {
        if (array_key_exists($name, $this->metadata)) {
            return $this->metadata[$name];
        }
        return null;
    }

    public function update()
    {
        $product_count = 0;
        $product_total = 0;

        $discount_total = 0;
        $products_discounts_total = 0;
        $vouchers_discounts_total = 0;
        $shipping_discounts_total = 0;

        foreach ($this->products as $product) {
            $product_count += $product->quantity;
            $product_cost = $product->price * $product->quantity;
            $product_total += $product_cost;
            if ($product->discount_price && $product->discount_price > $product_price) {
                $discount_price = round($product->price, 2) - round($product->discount_price, 2);
                $products_discounts_total += $discount_price * $product->quantity;
                $discount = new Discount($product->discount_label, Discount::TYPE_DISCOUNT, Discount::DISCOUNT_TYPE_FLAT, $discount_price, Discount::PRODUCT_SCOPE_PRODUCT_CUSTOM, array($product->product_id));
                $this->discounts['discounts'][] = $discount;
                $this->product_prices[$product->product_id] = array(
                    'quantity' => $product->quantity,
                    'price' => $discount_price, // value is flat and precalculated from database from the batch job
                    'computedPrice' => $discount_price * $product->quantity,
                );
            } else {
                $this->product_prices[$product->product_id] = array(
                    'quantity' => $product->quantity,
                    'price' => $product->price, // value is flat and precalculated from database from the batch job
                    'computedPrice' => $product_cost,
                );
            }
        }
        $this->product_count = $product_count;
        $this->product_total = $product_total;

        $this->shipping = $this->shipping->calculate($this);


        foreach ($this->discounts['vouchers'] as $discount) {
            $discountApplication = $discount->getDiscountApplication($this->product_total, $this->shipping->cost);
            $vouchers_discounts_total += $discountApplication['total'];
            $shipping_discounts_total += $discountApplication['shipping'];
            if (!empty($discountApplication['products'])) {
                foreach ($discountApplication['products'] as $productId => $productDiscount) {
                    $this->product_prices[$productId]['price'] -= $productDiscount;
                    $this->product_prices[$productId]['computedPrice'] = $this->product_prices[$productId]['price'] * $this->product_prices[$productId]['quantity'];
                    $vouchers_discounts_total += $productDiscount * $this->product_prices[$productId]['quantity'];
                }
            }
        }



        $this->products_discounts_total = round($products_discounts_total, 2);
        $this->vouchers_discounts_total = round($vouchers_discounts_total, 2);
        $this->shipping_discounts_total = round($shipping_discounts_total, 2);

        $discount_total = round($products_discounts_total + $vouchers_discounts_total + $shipping_discounts_total, 2);
        $this->discount_total = $discount_total;

        $this->total = round($this->product_total - $this->discount_total + $this->shipping->cost, 2);
        // taxe selon le pays
        // $this->tax = $this->taxService->calculate($this);
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
