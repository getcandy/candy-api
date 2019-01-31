<?php

namespace GetCandy\Api\Core\Orders\Factories;

use DB;
use Illuminate\Foundation\Auth\User;
use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Orders\Models\OrderDiscount;
use GetCandy\Api\Core\Orders\Events\OrderSavedEvent;
use GetCandy\Api\Core\Shipping\Models\ShippingPrice;
use GetCandy\Api\Core\Settings\Services\SettingService;
use GetCandy\Api\Core\Orders\Interfaces\OrderFactoryInterface;
use GetCandy\Api\Core\Orders\Exceptions\BasketHasPlacedOrderException;
use GetCandy\Api\Core\Currencies\Interfaces\CurrencyConverterInterface;
use GetCandy\Api\Core\Pricing\PriceCalculatorInterface;

class OrderFactory implements OrderFactoryInterface
{
    /**
     * The basket model
     *
     * @var Basket
     */
    protected $basket;

    /**
     * The order model instance
     *
     * @var Order
     */
    protected $order;

    /**
     * The associated user
     *
     * @var \Illuminate\Foundation\Auth\User
     */
    protected $user;

    /**
     * The site settings provider
     *
     * @var SettingService
     */
    protected $settings;

    /**
     * The shipping model instance
     *
     * @var ShippingPrice
     */
    protected $shipping;

    /**
     * The shipping preference
     *
     * @var null|string
     */
    protected $shippingPreference;

    /**
     * The currencies instance
     *
     * @var CurrencyConverterInterface
     */
    protected $currencies;

    /**
     * The price calculator instance
     *
     * @var PriceCalculatorInterface
     */
    protected $calculator;

    public function __construct(
        SettingService $settings,
        CurrencyConverterInterface $currencies,
        PriceCalculatorInterface $calculator
    ) {
        $this->settings = $settings;
        $this->currencies = $currencies;
        $this->calculator = $calculator;
    }

    /**
     * Set the value for user
     *
     * @param User $user
     * @return self
     */
    public function user(User $user = null)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Set the value for basket
     *
     * @param Basket $basket
     * @return self
    */
    public function basket(Basket $basket)
    {
        $this->basket = $basket;

        if ($basket->user) {
            $this->user($basket->user);
        }

        return $this;
    }

    /**
     * Set the value of order
     *
     * @param Order $order
     * @return self
     */
    public function order(Order $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Get the value for basket
     *
     * @return Basket
     */
    public function getBasket()
    {
        return $this->basket;
    }

    /**
     * Get the value for user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Resolve the basket to an order
     *
     * @return Order
     */
    public function resolve()
    {
        if (!$this->order) {
            $order = $this->getActiveOrder();
        } else {
            $order = $this->order;
        }

        if (!$this->basket) {
            $this->basket = $order->basket;
        }

        if ($this->user) {
            $order->user()->associate($this->user);
            $this->setUserFields($order);
        }

        $order->conversion = $this->currencies->set($this->basket->currency)->rate();
        $order->currency = $this->basket->currency;

        $order->save();

        $this->resolveDiscounts($order);
        $this->resolveLines($order);

        if ($this->shipping) {
            $this->addShippingLine($order);
        }

        event(new OrderSavedEvent($order, $this->basket));

        return $this->recalculate($order);
    }

    /**
     * Resolve the lines for our order
     *
     * @param Order $order
     * @return Order
     */
    protected function resolveLines($order)
    {
        $order->basketLines()->delete();
        $lines = [];
        foreach ($this->basket->lines as $line) {
            array_push($lines, [
                'product_variant_id' => $line->variant->id,
                'sku' => $line->variant->sku,
                'tax_total' => $line->total_tax * 100,
                'tax_rate' => $line->variant->tax->percentage,
                'discount_total' => $line->discount_total * 100 ?? 0,
                'line_total' => $line->total_cost * 100,
                'unit_price' => $line->base_cost * 100,
                'unit_qty' => $line->variant->unit_qty,
                'quantity' => $line->quantity,
                'description' => $line->variant->product->attribute('name'),
                'option' => $line->variant->name,
            ]);
        }
        $order->lines()->createMany($lines);
        return $order;
    }


    /**
     * Get the active order
     *
     * @return Order
     */
    protected function getActiveOrder()
    {
        if ($this->basket->activeOrder) {
            return $this->basket->activeOrder;
        } elseif ($this->basket->placedOrder) {
            throw new BasketHasPlacedOrderException;
        }
        return $this->createNewOrder();
    }

    /**
     * Create a new order
     *
     * @return Order
     */
    protected function createNewOrder()
    {
        $order = new Order;

        $order->basket()->associate($this->basket);

        $this->basket->order;
        if ($this->settings->get('orders')) {
            $order->status = $settings->content['default_status'] ?? 'awaiting-payment';
        }

        return $order;
    }

    /**
     * Sets the fields for contact info on the order.
     *
     * @param string $order
     * @param array $fields
     * @param string $prefix
     *
     * @return void
     */
    protected function setFields($order, $fields, $prefix)
    {
        $attributes = [];
        foreach ($fields as $field => $value) {
            $attributes[$prefix.'_'.$field] = $value;
        }
        $order->fill($attributes);
    }

    /**
     * Sets the user fields
     *
     * @param Order $order
     * @return void
     */
    protected function setUserFields(&$order)
    {
        foreach ($this->user->addresses as $address) {
            $this->setFields($order, $address->fields, $address->billing ? 'billing' : 'shipping');
        }
        return $order;
    }


    /**
     * Recalculates an orders totals.
     *
     * @param Order $order
     * @return void
     */
    public function recalculate($order)
    {
        $totals = DB::table('order_lines')->select(
            'order_id',
            // DB::RAW('SUM(line_total) as line_total'),
            DB::RAW('SUM(line_total) as line_total'),
            DB::RAW('SUM(delivery_total) as delivery_total'),
            DB::RAW('SUM(tax_total) as tax_total'),
            DB::RAW('SUM(discount_total) as discount_total'),
            DB::RAW('SUM(discount_total) as tax_discount_total'),
            DB::RAW('SUM(line_total) + SUM(tax_total) + SUM(delivery_total) as grand_total')
        )->where('order_id', '=', $order->id)
        ->where('is_shipping', '=', false)->groupBy('order_id')->first();

        // If we don't have any totals, then we must have had an order already and deleted all the lines
        // from it and gone back to the checkout.
        if (! $totals) {
            $totals = new \stdClass;
            $totals->line_total = 0;
            $totals->tax_total = 0;
            $totals->delivery_total = 0;
            $totals->discount_total = 0;
            $totals->grand_total = 0;
        }

        $shipping = $order->lines()
            ->select(
                'line_total',
                'tax_total',
                'discount_total',
                DB::RAW('line_total + tax_total - discount_total as grand_total')
            )->whereIsShipping(true)->first();

        if ($shipping) {
            $totals->delivery_total += $shipping->line_total;
            $totals->tax_total += $shipping->tax_total;
            $totals->discount_total += $shipping->discount_total;
            $totals->grand_total += $shipping->grand_total;
        }

        $order->update([
            'delivery_total' => $totals->delivery_total ?? 0,
            'tax_total' => $totals->tax_total ?? 0,
            'discount_total' => $totals->discount_total ?? 0,
            'sub_total' => $totals->line_total ?? 0,
            'order_total' => $totals->grand_total ?? 0,
        ]);

        return $order;
    }

    /**
     * Set the value for shipping price with preference
     *
     * @param ShippingPrice $price
     * @param string $preference
     * @return self
     */
    public function shipping(ShippingPrice $price, $preference = null)
    {
        $this->shipping = $price;
        $this->preference = $preference;
        return $this;
    }

    /**
     * Adds a shipping line to an order.
     *
     * @param string $orderId
     * @param string $shippingPriceId
     * @param string $preference
     *
     * @return Order
     */
    protected function addShippingLine($order)
    {
        $updateFields = [
            'shipping_method' => $this->shipping->method->name,
        ];

        if ($this->preference) {
            $updateFields['shipping_preference'] = $this->preference;
        }

        $order->update($updateFields);

        $tax = app('api')->taxes()->getDefaultRecord();

        $rate = $this->calculator->get(
            $this->shipping->rate,
            $tax->percentage
        );

        $basket = $order->basket;

        // Remove any shipping lines already on there.
        $existing = $order->lines()->where('is_shipping', '=', true)->first();

        if ($existing) {
            $existing->delete();
        }

        // Does the basket have a free shipping discount?
        $discounts = $order->basket->discounts;

        $order->lines()->create([
            'is_shipping' => true,
            'quantity' => $rate->qty,
            'discount_total' => $basket->freeShipping ? $rate->amount + $rate->tax : 0,
            'description' => $this->shipping->method->attribute('name'),
            'line_total' => $rate->total_cost,
            'unit_price' => $rate->unit_cost,
            'option' => $this->shipping->zone->name,
            'tax_total' => $rate->total_tax,
            'tax_rate' => $tax->percentage,
            'sku' => $this->shipping->encodedId(),
        ]);

        event(new OrderSavedEvent($order->refresh()));

        return $order;
    }

    /**
     * Resolve the discounts to an order
     *
     * @param Order $order
     * @return Order
     */
    protected function resolveDiscounts($order)
    {
        $order->discounts()->delete();
        $basket = $this->basket;

        foreach ($basket->discounts as $discount) {
            // Get the eligibles.
            foreach ($discount->sets as $set) {
                foreach ($set->items as $item) {
                    $quantity = 0;

                    if ($item->type == 'product') {
                        $matched = $basket->lines->filter(function ($line) use ($item) {
                            return $item->products->contains($line->variant->product);
                        });
                        foreach ($matched as $match) {
                            $quantity += $match->quantity;
                        }
                    } else {
                        $quantity = 1;
                    }
                    if ($item->type == 'coupon') {
                        $coupon = new OrderDiscount([
                            'coupon' => $item->value,
                            'order_id' => $order->id,
                            'name' => $discount->attribute('name'),
                            'type' => 'coupon',
                        ]);

                        $total = 0;

                        foreach ($discount->rewards as $reward) {
                            if ($reward->type == 'percentage') {
                                $total += (($basket->sub_total + $basket->discount_total) * $reward->value) / 100;
                            }
                        }

                        $coupon->amount = $total * 100;

                        $coupon->save();
                    }

                    foreach ($discount->rewards as $reward) {
                        if ($reward->type == 'product') {
                            foreach ($reward->products as $product) {
                                $variant = $this->variants->init(
                                    $product->product->variants->first()
                                )->get();

                                // Work out how many times we need to add this product.
                                $quantity = floor($quantity / $discount->lower_limit);

                                $order->lines()->create([
                                    'sku' => $variant->sku,
                                    'tax_total' => ($variant->total_tax * $quantity) * 100,
                                    'tax_rate' => $variant->tax->percentage,
                                    'discount_total' => (($variant->total_price * 100) + ($variant->total_tax * 100)) * $quantity,
                                    'line_total' => (($variant->total_price * $quantity) * 100),
                                    'unit_price' => $variant->unit_cost * 100,
                                    'unit_qty' => $variant->unit_qty,
                                    'quantity' => $quantity,
                                    'description' => $product->product->attribute('name'),
                                    'variant' => $variant->name,
                                ]);
                            }
                        }
                    }
                }
            }
        }

        $order->discount_total = $order->lines()->sum('discount_total');

        return $order;
    }
}
