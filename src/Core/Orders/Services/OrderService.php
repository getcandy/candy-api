<?php

namespace GetCandy\Api\Core\Orders\Services;

use DB;
use PDF;
use Carbon\Carbon;
use PriceCalculator;
use CurrencyConverter;
use GetCandy\Api\Core\Auth\Models\User;
use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Orders\Events\OrderSavedEvent;
use GetCandy\Api\Core\Orders\Events\OrderProcessedEvent;
use GetCandy\Api\Core\Orders\Events\OrderBeforeSavedEvent;
use GetCandy\Api\Core\Orders\Exceptions\IncompleteOrderException;

class OrderService extends BaseService
{
    /**
     * @var Basket
     */
    protected $model;

    public function __construct()
    {
        $this->model = new Order();
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
        $basket = app('api')->baskets()->getByHashedId($basketId);

        app('api')->baskets()->setTotals($basket);

        if ($basket->activeOrder) {
            $order = $basket->activeOrder;
        } else {
            $order = new Order;
            $order->basket()->associate($basket);

            // Get the default order status
            $settings = app('api')->settings()->get('orders');

            if ($settings) {
                $order->status = $settings->content['default_status'];
            }
        }

        if ($user) {
            $order->user()->associate($user);
            foreach ($user->addresses as $address) {
                $this->setFields($order, $address->fields, $address->billing ? 'billing' : 'shipping');
            }
        }

        $order->conversion = CurrencyConverter::rate();
        $order->currency = $basket->currency;

        $order->save();

        $order->discounts()->delete();

        foreach ($order->basketLines as $line) {
            $line->delete();
        }

        $order->discounts()->createMany(
            $this->mapOrderDiscounts($basket)
        );

        $order->lines()->createMany(
            $this->mapOrderLines($basket)
        );

        $order->discount_total = $order->lines()->sum('discount_total');

        event(new OrderSavedEvent($order));

        return $order;
    }

    /**
     * Adds a shipping line to an order.
     *
     * @param string $orderId
     * @param string $shippingPriceId
     *
     * @return Order
     */
    public function addShippingLine($orderId, $shippingPriceId)
    {
        $order = $this->getByHashedId($orderId);
        $price = app('api')->shippingPrices()->getByHashedId($shippingPriceId);

        // TODO Need a better way to do this basket totals thing
        $basket = $order->basket;

        app('api')->baskets()->setTotals($basket);

        $tax = app('api')->taxes()->getDefaultRecord();

        $rate = PriceCalculator::get(
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
            'quantity' => 1,
            'discount_total' => $basket->freeShipping ? $rate->amount + $rate->tax : 0,
            'description' => $price->method->attribute('name'),
            'line_total' => $rate->amount,
            'unit_price' => $rate->amount,
            'variant' => $price->zone->name,
            'tax_total' => $rate->tax,
            'tax_rate' => $tax->percentage,
            'sku' => $shippingPriceId,
        ]);

        event(new OrderSavedEvent($order));

        return $order;
    }

    /**
     * Update an order.
     *
     * @param string $orderId
     * @param array $data
     *
     * @return Order
     */
    public function update($orderId, array $data)
    {
        $order = $this->getByHashedId($orderId);

        if (! empty($data['tracking_no'])) {
            $order->tracking_no = $data['tracking_no'];
        }

        if (! empty($data['status'])) {
            $order->status = $data['status'];
        }

        if (strtolower($order->status) == 'dispatched') {
            $order->dispatched_at = Carbon::now();
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
            unset($data['vat_no']);
        }

        $order->save();

        // If this address doesn't exist, create it.
        if (! empty($data['address_id'])) {
            $shipping = app('api')->addresses()->getByHashedId($data['address_id']);
            $data = $shipping->toArray();
        } elseif ($user) {
            $address = app('api')->addresses()->addAddress($user, $data, $type);
            $data = $address->fields;
        }

        if ($user) {
            $order->shipping_phone = $user->contact_number;
            $order->billing_phone = $user->contact_number;
        }

        $this->setFields($order, $data, $type);

        $order->save();

        event(new OrderSavedEvent($order));

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
        $attributes = $order->getAttributes();
        foreach ($fields as $handle => $value) {
            if ($handle == 'channel') {
                continue;
            }
            $field = $prefix.'_'.$handle;
            if (array_key_exists($field, $attributes)) {
                $order->setAttribute($field, $value);
            }
        }
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

        return $query->findOrFail($id);
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
            $increment = $segments[2] + 1;
        }

        return $year.'-'.$month.'-'.str_pad($increment, 4, 0, STR_PAD_LEFT);
    }

    /**
     * Syncs a given basket with its order.
     *
     * @param Order $order
     * @param Basket $basket
     *
     * @return Order
     */
    public function syncWithBasket(Order $order, Basket $basket)
    {
        $order->lines()->delete();
        $order->discounts()->delete();

        $order->discounts()->createMany(
            $this->mapOrderDiscounts($basket)
        );

        $order->lines()->createMany(
            $this->mapOrderLines($basket)
        );

        $order->save();

        event(new OrderSavedEvent($order));

        return $order;
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
            $tax = $line->current_tax;
            $currentTotal = $line->current_total;

            $tax = PriceCalculator::get(
                $currentTotal - $line->discount,
                $line->variant->tax
            )->tax;
            array_push($lines, [
                'sku' => $line->variant->sku,
                'tax_total' => round($tax, 2) * 100,
                'tax_rate' => $line->variant->tax->percentage,
                'discount_total' => $line->discount ?? 0,
                'line_total' => round($currentTotal, 2) * 100,
                'unit_price' => round($currentTotal / $line->quantity, 2) * 100,
                'quantity' => $line->quantity,
                'description' => $line->variant->product->attribute('name'),
                'variant' => $line->variant->name,
            ]);
        }

        return $lines;
    }

    /**
     * Maps an orders discounts from a basket.
     *
     * @param Basket $basket
     *
     * @return array
     */
    protected function mapOrderDiscounts($basket)
    {
        $discounts = [];

        foreach ($basket->discounts as $discount) {
            $amount = 0;
            foreach ($discount->rewards as $reward) {
                array_push($discounts, [
                    'coupon' => $discount->pivot->coupon,
                    'name' => $discount->attribute('name'),
                    'description' => $discount->attribute('description'),
                    'type' => $reward->type,
                    'value' => $reward->value,
                ]);
            }
        }

        return $discounts;
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

        if (! $this->isProcessable($order)) {
            throw new IncompleteOrderException;
        }

        $order->notes = $data['notes'] ?? null;
        $order->customer_reference = $data['customer_reference'] ?? null;

        if (! empty($data['payment_type_id'])) {
            $type = app('api')->paymentTypes()->getByHashedId($data['payment_type_id']);
        }

        $result = app('api')->payments()->charge(
            $order,
            $data['payment_token'] ?? null,
            $type ?? null,
            $data['data'] ?? []
        );

        if ($result) {
            if (! empty($type)) {
                $order->status = $type->success_status;
            } else {
                $order->status = 'payment-processing';
            }
            $order->reference = $this->getNextInvoiceReference();
            $order->placed_at = Carbon::now();
        } else {
            $order->status = 'failed';
        }

        $order->save();

        event(new OrderProcessedEvent($order));

        return $order;
    }

    /**
     * Get paginated orders.
     *
     * @param int $length
     * @param int $page
     * @param User $user
     * @return void
     */
    public function getPaginatedData($length = 50, $page = 1, $user = null, $status = null, $keywords = null)
    {
        $query = $this->model
            ->withoutGlobalScope('open')
            ->withoutGlobalScope('not_expired');

        if (! $status || $status == 'processed') {
            $query = $query->whereNotIn('status', ['open', 'awaiting-payment']);
        } else {
            $query = $query->where('status', '=', $status);
        }

        if ($status == 'awaiting-payment') {
            $query = $query->orderBy('created_at', 'desc');
        } else {
            $query = $query->orderBy('placed_at', 'desc');
        }

        if ($keywords) {
            $query = $query->search($keywords);
        }

        if (! app('auth')->user()->hasRole('admin')) {
            $query = $query->whereHas('user', function ($q) use ($user) {
                $q->whereId($user->id);
            });
        }

        return $query->paginate($length, ['*'], 'page', $page);
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

        $pdf = PDF::loadView('pdf.order-invoice', $data);

        return $pdf;
    }
}
