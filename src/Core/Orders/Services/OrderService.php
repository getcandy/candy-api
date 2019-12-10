<?php

namespace GetCandy\Api\Core\Orders\Services;

use DB;
use PDF;
use Auth;
use Carbon\Carbon;
use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Orders\Models\OrderDiscount;
use GetCandy\Api\Core\Orders\Events\OrderSavedEvent;
use GetCandy\Api\Core\Orders\Jobs\OrderNotification;
use GetCandy\Api\Core\Baskets\Services\BasketService;
use GetCandy\Api\Core\Payments\Services\PaymentService;
use GetCandy\Api\Core\Pricing\PriceCalculatorInterface;
use GetCandy\Api\Core\Orders\Events\OrderProcessedEvent;
use GetCandy\Api\Core\Orders\Events\OrderBeforeSavedEvent;
use GetCandy\Api\Core\Orders\Interfaces\OrderServiceInterface;
use GetCandy\Api\Core\Products\Factories\ProductVariantFactory;
use GetCandy\Api\Core\Orders\Exceptions\IncompleteOrderException;
use GetCandy\Api\Core\Orders\Exceptions\BasketHasPlacedOrderException;
use GetCandy\Api\Core\Currencies\Interfaces\CurrencyConverterInterface;
use GetCandy\Api\Core\ActivityLog\Interfaces\ActivityLogFactoryInterface;
use GetCandy\Api\Core\Orders\Interfaces\OrderFactoryInterface;

class OrderService extends BaseService implements OrderServiceInterface
{
    /**
     * The basket service.
     *
     * @var BasketService
     */
    protected $baskets;

    /**
     * @var Basket
     */
    protected $model;

    /**
     * The payments service.
     *
     * @var PaymentService
     */
    protected $payments;

    /**
     * The price calculator instance.
     *
     * @var CurrencyConverterInterface
     */
    protected $currencies;

    /**
     * The price calculator instance.
     *
     * @var PriceCalculatorInterface
     */
    protected $calculator;

    public function __construct(
        BasketService $baskets,
        PaymentService $payments,
        ProductVariantFactory $variants,
        CurrencyConverterInterface $currencies,
        PriceCalculatorInterface $calculator,
        ActivityLogFactoryInterface $activity
    ) {
        $this->model = new Order();
        $this->baskets = $baskets;
        $this->payments = $payments;
        $this->variants = $variants;
        $this->currencies = $currencies;
        $this->calculator = $calculator;
        $this->activity = $activity;
    }

    /**
     * Stores an order.
     *
     * @param string $basketId
     *
     * @return Order
     */
    public function store($basketId, $user = null)
    {
        // Get the basket
        $basket = $this->baskets->getByHashedId($basketId);

        if ($basket->activeOrder) {
            $order = $basket->activeOrder;
        } elseif ($basket->placedOrder) {
            throw new BasketHasPlacedOrderException;
        } else {
            $order = new Order;
            $order->basket()->associate($basket);

            // Get the default order status
            $settings = app('api')->settings()->get('orders');

            if ($settings) {
                $order->status = $settings->content['default_status'] ?? 'awaiting-payment';
            }
        }

        if ($user) {
            $order->user()->associate($user);
            foreach ($user->addresses as $address) {
                $this->setFields($order, $address->fields, $address->billing ? 'billing' : 'shipping');
            }
        }

        $order->conversion = $this->currencies->set($basket->currency)->rate();
        $order->currency = $basket->currency;

        $order->save();

        $order->discounts()->delete();

        foreach ($order->basketLines as $line) {
            $line->delete();
        }

        $this->processDiscountLines($basket, $order);

        $order->lines()->createMany(
            $this->mapOrderLines($basket)
        );

        $order->discount_total = $order->lines()->sum('discount_total');

        event(new OrderSavedEvent($order, $basket));

        $order->load([
            'discounts',
            'lines.variant.product.assets.transforms',
        ]);

        return $order;
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
    public function addShippingLine($orderId, $shippingPriceId, $preference = null)
    {
        $order = $this->getByHashedId($orderId);

        $price = app('api')->shippingPrices()->getByHashedId($shippingPriceId);

        $updateFields = [
            'shipping_method' => $price->method->name,
        ];

        if ($preference) {
            $updateFields['shipping_preference'] = $preference;
        }

        $order->update($updateFields);

        $basket = $this->baskets->getForOrder($order);

        $tax = app('api')->taxes()->getDefaultRecord();

        $rate = $this->calculator->get(
            $price->rate,
            $tax->percentage
        );

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
            'description' => $price->method->attribute('name'),
            'line_total' => $rate->total_cost,
            'unit_price' => $rate->unit_cost,
            'option' => $price->zone->name,
            'tax_total' => $rate->total_tax,
            'tax_rate' => $tax->percentage,
            'sku' => $shippingPriceId,
        ]);

        event(new OrderSavedEvent($order->refresh()));

        return $order;
    }

    /**
     * Bulk update an order.
     *
     * @param array $orderIds
     * @param string $field
     * @param string $value
     * @throws \Illuminate\Database\QueryException
     * @return void
     */
    public function bulkUpdate($orderIds, $field, $value, $sendEmails = true, $data = [])
    {
        $realIds = $this->getDecodedIds($orderIds);

        $query = Order::withoutGlobalScopes()->whereIn('id', $realIds);

        $orders = $query->get();

        $orders->each(function ($order) use ($field, $value) {
            $this->activity->as(Auth::user())
                ->action('status-update')
                ->against($order)
                ->with([
                    'previous' => $order->{$field},
                    'new' => $value,
                ])->log();
        });

        $payload = [
            $field => $value,
        ];


        $result = $query->update($payload);

        if (! $result) {
            throw \InvalidArgumentException;
        }

        if ($field == 'status') {
            // If this status is our dispatched status, update the dispatched at.
            $dispatchedStatus = config('getcandy.orders.statuses.dispatched');

            if ($dispatchedStatus == $value) {
                $result = $query->update([
                    'dispatched_at' => Carbon::now(),
                ]);
            }

            if ($sendEmails) {
                $orders->each(function ($order) use ($value, $data) {
                    OrderNotification::dispatch(
                        $order,
                        $value,
                        $data
                    );
                });
            }
        }
    }

    /**
     * Update an order.
     *
     * @param string $orderId
     * @param array $data
     *
     * @return Order
     */
    public function update($orderId, array $data, $sendEmails = true, $emailContent = [])
    {
        $order = $this->getByHashedId($orderId);

        if (array_key_exists('tracking_no', $data)) {
            $order->tracking_no = $data['tracking_no'];
        }

        if (! empty($data['status'])) {

            $this->activity->as(Auth::user())
                ->action('status-update')
                ->against($order)
                ->with([
                    'previous' => $order->status,
                    'new' => $data['status'],
                ])->log();

            $order->status = $data['status'];

            $dispatchedStatus = config('getcandy.orders.statuses.dispatched');

            if ($dispatchedStatus == $order->status) {
                $order->dispatched_at = Carbon::now();
            }

            if ($sendEmails) {
                OrderNotification::dispatch(
                    $order,
                    $data['status'],
                    $emailContent
                );
            }
        }

        event(new OrderBeforeSavedEvent($order));
        $order->save();
        event(new OrderSavedEvent($order));

        return $order;
    }

    /**
     * Set the delivery details.
     *
     * @param string $id
     * @param array $data
     *
     * @return Order
     */
    public function setShipping($id, array $data, $user = null)
    {
        return $this->addAddress(
            $id,
            $data,
            'shipping',
            $user
        );
    }

    public function refresh($orderId)
    {
        $order = $this->getByHashedId($orderId);

        $refreshedOrder = $this->store(
            $order->basket->encodedId()
        );

        $refreshedOrder->save();

        event(new OrderSavedEvent($order));
    }

    /**
     * Recalculates an orders totals.
     *
     * @param Order $order
     * @return void
     */
    public function recalculate($order)
    {
        $totals = \DB::table('order_lines')->select(
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
     * Set the delivery details.
     *
     * @param string $id
     * @param array $data
     *
     * @return Order
     */
    public function setBilling($id, array $data, $user = null)
    {
        return $this->addAddress(
            $id,
            $data,
            'billing',
            $user
        );
    }

    /**
     * Adds an address for an order.
     *
     * @param string $id
     * @param array $data
     * @param string $type
     *
     * @return Order
     */
    protected function addAddress($id, $data, $type, $user = null)
    {
        $order = $this->getByHashedId($id);

        if (! empty($data['vat_no'])) {
            $order->vat_no = $data['vat_no'];
        }

        unset($data['vat_no']);
        unset($data['force']);

        $order->save();

        // If this address doesn't exist, create it.
        if (! empty($data['address_id'])) {
            $shipping = app('api')->addresses()->getByHashedId($data['address_id']);
            $payload = $shipping->only([
                'firstname',
                'lastname',
                'address',
                'address_two',
                'address_three',
                'city',
                'county',
                'state',
                'country',
                'zip',
            ]);
            $payload['email'] = $data['email'] ?? null;
            $payload['phone'] = $data['phone'] ?? null;
            $data = $payload;
        } elseif ($user) {
            app('api')->addresses()->addAddress($user, $data, $type);
        }

        $this->setFields($order, $data, $type);

        $order->save();

        event(new OrderSavedEvent($order));

        $this->recalculate($order);

        return $order;
    }

    /**
     * Sets the delivery price on an.
     *
     * @param string $orderId
     * @param string $priceId
     *
     * @return Order
     */
    public function setDeliveryPrice($orderId, $priceId)
    {
        return $this->setShippingCost($orderId, $priceId);
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
        $current = $order->getDetails($prefix);

        foreach ($current as $key => $value) {
            if (empty($fields[$key])) {
                $fields[$key] = null;
            }
        }

        $attributes = [];
        foreach ($fields as $field => $value) {
            $attributes[$prefix.'_'.$field] = $value;
        }

        $order->fill($attributes);
    }

    /**
     * Expires an order.
     *
     * @param string $orderId
     *
     * @return void
     */
    public function expire($orderId)
    {
        $order = $this->getByHashedId($orderId);

        $order->status = 'expired';
        $order->save();

        event(new OrderSavedEvent($order));

        return true;
    }

    public function getByHashedId($id)
    {
        $id = $this->model->decodeId($id);
        $query = $this->model->withoutGlobalScope('open')->withoutGlobalScope('not_expired');

        return $query->with(['lines.variant', 'transactions', 'discounts'])->findOrFail($id);
    }

    /**
     * Get the next invoice reference.
     *
     * @return string
     */
    protected function getNextInvoiceReference($year = null, $month = null)
    {
        if (! $year) {
            $year = (string) Carbon::now()->year;
        }

        if (! $month) {
            $month = Carbon::now()->format('m');
        }

        $order = DB::table('orders')->
            select(
                DB::RAW('MAX(reference) as reference')
            )->whereYear('placed_at', '=', $year)
            ->whereMonth('placed_at', '=', $month)
            ->first();

        if (! $order || ! $order->reference) {
            $increment = 1;
        } else {
            $segments = explode('-', $order->reference);

            if (count($segments) == 1) {
                $increment = 1;
            } else {
                $increment = end($segments) + 1;
            }
        }

        return $year.'-'.$month.'-'.str_pad($increment, 4, 0, STR_PAD_LEFT);
    }

    /**
     * Syncs a given basket with its order.
     *
     * @param Order $order
     * @param Basket $basket
     * @deprecated 0.3.35
     *
     * @return Order
     */
    public function syncWithBasket(Order $order, Basket $basket)
    {
        app(OrderFactoryInterface::class)
            ->basket($basket)
            ->order($order)
            ->resolve();
    }

    /**
     * Maps the order lines from a basket.
     *
     * @param Basket $basket
     *
     * @return void
     */
    protected function mapOrderLines($basket)
    {
        $lines = [];
        foreach ($basket->lines as $line) {
            array_push($lines, [
                'product_variant_id' => $line->variant->id,
                'sku' => $line->variant->sku,
                'tax_total' => $line->total_tax * 100,
                'tax_rate' => $line->variant->tax->percentage,
                'discount_total' => $line->discount_total ?? 0,
                'line_total' => $line->total_cost * 100,
                'unit_price' => $line->base_cost * 100,
                'unit_qty' => $line->variant->unit_qty,
                'quantity' => $line->quantity,
                'description' => $line->variant->product->attribute('name'),
                'option' => $line->variant->name,
            ]);
        }

        return $lines;
    }

    /**
     * Determines whether an active order exists with this id.
     *
     * @param string $orderId
     *
     * @return bool
     */
    public function isActive($orderId)
    {
        $realId = $this->getDecodedId($orderId);

        return (bool) $this->model->where('id', '=', $realId)->where('status', '=', 'awaiting-payment')->exists();
    }

    /**
     * Checks whether an order is processable.
     *
     * @param Order $order
     *
     * @return bool
     */
    protected function isProcessable(Order $order)
    {
        $fields = $order->required->filter(function ($field) use ($order) {
            return $order->getAttribute($field);
        });

        return $fields->count() === $order->required->count();
    }

    /**
     * Process an order for payment.
     *
     * @param array $data
     * @return mixed
     */
    public function process(array $data)
    {
        $order = $this->getByHashedId($data['order_id']);

        if (! $this->isProcessable($order) && empty($data['force'])) {
            throw new IncompleteOrderException;
        }

        $order->notes = $data['notes'] ?? null;
        $order->customer_reference = $data['customer_reference'] ?? null;
        $order->type = $data['type'] ?? null;

        $order->save();

        if (! empty($data['payment_type_id'])) {
            $type = app('api')->paymentTypes()->getByHashedId($data['payment_type_id']);
        } elseif (! empty($data['payment_type'])) {
            $type = app('api')->paymentTypes()->getByHandle($data['payment_type']);
        } else {
            $type = null;
        }

        $result = $this->payments->process(
            $order,
            $data['payment_token'] ?? null,
            $type ?? null,
            $data['data'] ?? []
        );

        return $this->handleProcessResponse($result, $order, $type);
    }

    /**
     * Handles the response from an order being processed.
     *
     * @param Transaction $transaction
     * @param Order $order
     * @return void
     */
    protected function handleProcessResponse($transaction, $order, $type = null)
    {
        if ($transaction->success) {
            if ($type) {
                $order->status = $type->success_status;
            } else {
                $order->status = config('getcandy.orders.statuses.pending', 'payment-processing');
            }

            $callback = config('getcandy.orders.reference_callback', null);

            if ($callback && $callback instanceof \Closure) {
                $order->reference = $callback($order);
            } else {
                $order->reference = $this->getNextInvoiceReference();
            }

            $order->placed_at = Carbon::now();

            $order->save();

            OrderNotification::dispatch(
                $order,
                $order->status
            );
        } else {
            $order->status = 'failed';
            $order->save();
        }
        event(new OrderProcessedEvent($order));

        return $order;
    }

    /**
     * Process a 3DSecured transaction.
     *
     * @param Order $order
     * @param string $transactionId
     * @param string $paRes
     * @param string $type
     * @return Order
     */
    public function processThreeDSecure($order, $transactionId, $paRes, $type = null)
    {
        $result = $this->payments->validateThreeD(
            $order,
            $transactionId,
            $paRes,
            $type
        );

        return $this->handleProcessResponse($result, $order);
    }

    public function getPending()
    {
        return $this->model->withoutGlobalScopes()->where('status', '=', 'payment-processing')->get();
    }

    /**
     * Set the contact details on an order.
     *
     * @param string $orderId
     * @param array $data
     *
     * @return Order
     */
    public function setContact($orderId, array $data)
    {
        $order = $this->getByHashedId($orderId);

        if (! empty($data['email'])) {
            $order->contact_email = $data['email'];
        }

        if (! empty($data['phone'])) {
            $order->contact_phone = $data['phone'];
        }

        $order->save();

        return $order;
    }

    /**
     * Get the order types.
     *
     * @return array
     */
    public function getTypes()
    {
        return Order::select(\DB::raw('type as label'))->groupBy('type')->get();
    }

    public function getPdf($order)
    {
        $settings['address'] = app('api')->settings()->get('address')['content'];
        $settings['tax'] = app('api')->settings()->get('tax')['content'];
        $settings['contact'] = app('api')->settings()->get('contact')['content'];

        $data = [
            'order' => $order->load(['lines', 'discounts']),
            'settings' => $settings,
        ];

        //TODO: This is bad mmkay, refactor when orders are re engineered

        foreach ($data['order']['discounts'] as $index => $discount) {
            $total = 0;
            foreach ($order->lines as $line) {
                if ($discount->type == 'percentage') {
                    $total += $line->total * ($discount->amount / 100);
                } elseif ($discount->type == 'fixed-price') {
                    $total += $line->total - $discount->amount;
                }
            }
            $discount->total = $total;
        }

        $pdf = PDF::loadView(config('getcandy.invoicing.pdf', 'hub::pdf.order-invoice'), $data);

        return $pdf;
    }

    /**
     * Process discount lines for an order.
     *
     * @param Basket $basket
     * @param Order $order
     * @return void
     */
    protected function processDiscountLines(Basket $basket, Order $order)
    {
        $order->discounts()->delete();
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
                        $bTotal = $basket->sub_total;
                        foreach ($discount->rewards as $reward) {
                            if ($reward->type == 'percentage') {
                                $total += $bTotal * $reward->value;
                            }
                        }

                        $coupon->amount = $total;

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

                                // So we get what line total is.
                                $lineTotal = (($variant->total_price * $quantity) * 100);

                                $order->lines()->create([
                                    'sku' => $variant->sku,
                                    'tax_total' => ($variant->total_tax * $quantity) * 100,
                                    'tax_rate' => $variant->tax->percentage,
                                    'discount_total' => (($variant->total_price * 100) + ($variant->total_tax * 100)) * $quantity,
                                    'line_total' => $lineTotal,
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
    }
}
