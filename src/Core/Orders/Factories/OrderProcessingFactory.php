<?php

namespace GetCandy\Api\Core\Orders\Factories;

use Carbon\Carbon;
use DB;
use GetCandy\Api\Core\Orders\Events\OrderProcessedEvent;
use GetCandy\Api\Core\Orders\Interfaces\OrderProcessingFactoryInterface;
use GetCandy\Api\Core\Orders\Jobs\OrderNotification;
use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Payments\Exceptions\InvalidPaymentTokenException;
use GetCandy\Api\Core\Payments\Exceptions\ThreeDSecureRequiredException;
use GetCandy\Api\Core\Payments\Models\PaymentType;
use GetCandy\Api\Core\Payments\PaymentContract;
use GetCandy\Api\Core\Payments\PaymentResponse;
use GetCandy\Api\Core\Payments\ThreeDSecureResponse;

class OrderProcessingFactory implements OrderProcessingFactoryInterface
{
    /**
     * The order instance.
     *
     * @var \GetCandy\Api\Core\Orders\Models\Order
     */
    protected $order;

    /**
     * Additional payload fields.
     *
     * @var array
     */
    protected $payload = [];

    /**
     * The payment provider.
     *
     * @var \GetCandy\Api\Core\Payments\Models\PaymentType
     */
    protected $provider;

    /**
     * The payment token nonce.
     *
     * @var string
     */
    protected $nonce;

    /**
     * The payment manager instance.
     *
     * @var \GetCandy\Api\Core\Payments\PaymentContract
     */
    protected $manager;

    /**
     * The order notes.
     *
     * @var string
     */
    protected $notes;

    /**
     * The order type.
     *
     * @var string
     */
    protected $type;

    /**
     * The order meta.
     *
     * @var array
     */
    protected $meta;

    /**
     * The company name for the order.
     *
     * @var string
     */
    protected $companyName;

    /**
     * The customer reference.
     *
     * @var string
     */
    protected $customerReference;

    public function __construct(PaymentContract $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Set the value for nonce.
     *
     * @param  string  $nonce
     * @return $this
     */
    public function nonce($nonce)
    {
        $this->nonce = $nonce;

        return $this;
    }

    public function meta($meta)
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * Bulk set the value for payload.
     *
     * @param array $payload
     * @return self
     */
    public function payload(array $payload)
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * Set the value for provider.
     *
     * @param  null|\GetCandy\Api\Core\Payments\Models\PaymentType  $provider
     * @return $this
     */
    public function provider(PaymentType $provider = null)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Set a value to the payload.
     *
     * @param  string  $reference
     * @return $this
     */
    public function set($key, $value)
    {
        $this->payload[$key] = $value;

        return $this;
    }

    /**
     * Set the value for type.
     *
     * @param  null|string  $type
     * @return $this
     */
    public function type($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set the value for notes.
     *
     * @param  null|string $notes
     * @return $this
     */
    public function notes($notes = null)
    {
        $this->notes = $notes;

        return $this;
    }

    public function customerReference($ref = null)
    {
        $this->customerReference = $ref;

        return $this;
    }

    public function companyName($name = null)
    {
        $this->companyName = $name;

        return $this;
    }

    /**
     * Set the value of order.
     *
     * @param  \GetCandy\Api\Core\Orders\Models\Order  $order
     * @return $this
     */
    public function order($order)
    {
        $this->order = $order;

        return $this;
    }

    public function resolve()
    {
        if ($this->order->placed_at) {
            throw new OrderAlreadyProcessedException;
        }

        $driver = $this->manager->with(
            $this->provider ? $this->provider->driver : null
        );

        if (! $driver->validate($this->nonce)) {
            throw new InvalidPaymentTokenException;
        }

        $this->order->notes = $this->notes;
        $this->order->customer_reference = $this->customerReference;
        $this->order->type = $this->type ?: $driver->getName();

        $this->order->meta = array_merge($this->order->meta ?? [], $this->meta ?? []);
        $this->order->company_name = $this->companyName;

        $this->order->save();

        $response = $driver
            ->token($this->nonce)
            ->order($this->order)
            ->fields($this->payload)
            ->charge();

        if ($response instanceof ThreeDSecureResponse) {
            throw new ThreeDSecureRequiredException($response);
        }

        return $this->processResponse($response);
    }

    /**
     * Handle the response from the payment driver.
     *
     * @param  \GetCandy\Api\Core\Payments\PaymentResponse  $response
     * @return \GetCandy\Api\Core\Orders\Models\Order
     */
    protected function processResponse(PaymentResponse $response)
    {
        if ($response->success) {
            if ($this->provider) {
                $this->order->status = $this->provider->success_status;
            } else {
                $this->order->status = config('getcandy.orders.statuses.pending', 'payment-processing');
            }

            $callback = config('getcandy.orders.reference_callback', null);

            if ($callback) {
                if ($callback instanceof \Closure) {
                    $this->order->reference = $callback($this->order);
                } elseif (class_exists($callback)) {
                    $this->order->reference = app($callback)->handle($this->order);
                }
            } else {
                $this->order->reference = $this->getNextInvoiceReference();
            }
            $this->order->placed_at = Carbon::now();
            $this->order->save();
            OrderNotification::dispatch(
                $this->order,
                $this->order->status
            );
        } else {
            $this->order->status = 'failed';
            $this->order->save();
        }

        event(new OrderProcessedEvent($this->order));

        return $this->order;
    }

    /**
     * Get the order instance.
     *
     * @return \GetCandy\Api\Core\Orders\Models\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Determines whether an order can be processed.
     *
     * @return bool
     */
    protected function canProcess()
    {
        $fields = $order->required->filter(function ($field) use ($order) {
            return $order->getAttribute($field);
        });

        return $fields->count() === $order->required->count();
    }

    /**
     * Get the next invoice reference.
     *
     * @return string
     */
    public function getNextInvoiceReference($year = null, $month = null)
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

        return config('getcandy.orders.reference_prefix', null).
            $year.
            '-'.
            $month.
            '-'.
            str_pad($increment, 4, 0, STR_PAD_LEFT);
    }
}
