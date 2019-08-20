<?php

namespace GetCandy\Api\Http\Controllers\Orders;

use Illuminate\Http\Request;
use GetCandy\Api\Core\Orders\OrderExport;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Resources\Files\PdfResource;
use GetCandy\Api\Http\Requests\Orders\CreateRequest;
use GetCandy\Api\Http\Requests\Orders\UpdateRequest;
use GetCandy\Api\Http\Requests\Orders\ProcessRequest;
use GetCandy\Api\Http\Resources\Orders\OrderResource;
use GetCandy\Api\Http\Resources\Orders\OrderCollection;
use GetCandy\Api\Http\Requests\Orders\BulkUpdateRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GetCandy\Api\Http\Requests\Orders\StoreAddressRequest;
use GetCandy\Api\Core\Payments\Services\PaymentTypeService;
use GetCandy\Api\Http\Resources\Orders\OrderExportResource;
use GetCandy\Api\Core\Shipping\Services\ShippingPriceService;
use GetCandy\Api\Core\Orders\Interfaces\OrderFactoryInterface;
use GetCandy\Api\Core\Orders\Interfaces\OrderServiceInterface;
use GetCandy\Api\Core\Shipping\Services\ShippingMethodService;
use GetCandy\Api\Http\Resources\Payments\ThreeDSecureResource;
use GetCandy\Api\Core\Orders\Interfaces\OrderCriteriaInterface;
use GetCandy\Api\Core\Baskets\Interfaces\BasketFactoryInterface;
use GetCandy\Api\Core\Baskets\Interfaces\BasketCriteriaInterface;
use GetCandy\Api\Core\Orders\Exceptions\IncompleteOrderException;
use GetCandy\Api\Http\Resources\Shipping\ShippingPriceCollection;
use GetCandy\Api\Http\Requests\Orders\Shipping\AddShippingRequest;
use GetCandy\Api\Core\Orders\Exceptions\BasketHasPlacedOrderException;
use GetCandy\Api\Core\Orders\Exceptions\OrderAlreadyProcessedException;
use GetCandy\Api\Core\Orders\Interfaces\OrderProcessingFactoryInterface;
use GetCandy\Api\Core\Payments\Exceptions\ThreeDSecureRequiredException;

class OrderController extends BaseController
{
    protected $orders;

    /**
     * The baskets criteria instance.
     *
     * @var BasketCriteriaInterface
     */
    protected $baskets;

    public function __construct(
        OrderCriteriaInterface $orders,
        BasketCriteriaInterface $baskets
    ) {
        $this->orders = $orders;
        $this->baskets = $baskets;
    }

    /**
     * Returns a listing of channels.
     * @return Json
     */
    public function index(Request $request)
    {
        $request->validate([
            'from' => 'date_format:Y-m-d',
            'to' => 'date_format:Y-m-d',
        ]);

        $criteria = $this->orders;

        $criteria->fill($request->all())
            ->include($request->includes ?: [])
            ->set('without_scopes', [
                'open',
                'not_expired',
            ]);

        if (!$request->status) {
            $criteria->set('scopes', [
                'placed',
            ]);
        }

        if ($request->user()->hasRole('admin') && ! $request->only_own) {
            $criteria->set('restrict', false);
        }
        $criteria->set('user', $request->user());

        $orders = $criteria->get();

        return new OrderCollection($orders);
    }

    public function getTypes(Request $request)
    {
        $types = app('api')->orders()->getTypes();

        return response()->json([
            'data' => $types,
        ]);
    }

    public function getExport(Request $request, OrderServiceInterface $orders)
    {
        $request->validate([
            'orders' => 'required|string',
            'format' => 'required',
        ]);

        $config = config('getcandy.orders.exports.'.$request->format, config('getcandy.orders.exports.csv'));

        if (! view()->exists($config['view'] ?? null)) {
            return $this->errorWrongArgs("View for \"{$request->format}\" not found.");
        }

        $orders = $orders->getByHashedIds(explode(':', $request->orders));

        $content = view($config['view'], ['orders' => $orders])->render();

        return new OrderExportResource(
            new OrderExport($content, $config['format'] ?? 'csv')
        );
    }

    /**
     * Handles the request to show a channel based on it's hashed ID.
     * @param  string $id
     * @return Json
     */
    public function show($id, Request $request)
    {
        try {
            $order = $this->orders
                ->set('without_scopes', ['open'])
                ->include($request->includes)
                ->id($id)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new OrderResource($order);
    }

    /**
     * Store either a new or existing basket.
     *
     * @param CreateRequest $request
     *
     * @return void
     */
    public function store(CreateRequest $request, OrderFactoryInterface $factory, BasketFactoryInterface $basketFactory)
    {
        $basket = $this->baskets->id($request->basket_id)->first();
        $basket = $basketFactory->init($basket)->get();

        try {
            $order = $factory->basket($basket)->type($request->type)->user($request->user())->resolve();
        } catch (BasketHasPlacedOrderException $e) {
            return $this->errorForbidden(trans('getcandy::exceptions.basket_already_has_placed_order'));
        }

        return new OrderResource($order->load($request->includes ?: []));
    }

    /**
     * Process an order.
     *
     * @param ProcessRequest $request
     *
     * @return json
     */
    public function process(
        ProcessRequest $request,
        OrderProcessingFactoryInterface $factory,
        OrderCriteriaInterface $criteria,
        PaymentTypeService $paymentTypes
    ) {
        try {
            // $order = $factory->
            $paymentType = null;

            if ($request->payment_type_id) {
                $type = $paymentTypes->getByHashedId($request->payment_type_id);
            } elseif ($request->payment_type) {
                $type = $paymentTypes->getByHandle($request->payment_type);
            } else {
                $type = null;
            }

            $order = $criteria->id($request->order_id)->first();

            if (! $order) {
                // Does this order exist, but has already been placed?
                $placedOrder = $criteria->id($request->order_id)->getBuilder()->withoutGlobalScopes()->first();
                if ($placedOrder && $placedOrder->placed_at) {
                    throw new OrderAlreadyProcessedException;
                }
            }

            $order = $factory
                ->order($order)
                ->provider($type)
                ->nonce($request->payment_token)
                ->type($request->type)
                ->customerReference($request->customer_reference)
                ->meta($request->meta ?? [])
                ->notes($request->notes)
                ->companyName($request->company_name)
                ->payload($request->data ?: [])
                ->resolve();

            if (! $order->placed_at) {
                return $this->errorForbidden('Payment has failed');
            }

            return new OrderResource($order);
        } catch (IncompleteOrderException $e) {
            return $this->errorForbidden('The order is missing billing information');
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        } catch (OrderAlreadyProcessedException $e) {
            return $this->errorUnprocessable('This order has already been processed');
        } catch (ThreeDSecureRequiredException $e) {
            return new ThreeDSecureResource($e->getResponse());
        }
    }

    public function bulkUpdate(BulkUpdateRequest $request)
    {
        try {
            app('api')->orders()->bulkUpdate(
                $request->orders,
                $request->field,
                $request->value,
                $request->send_emails ?: false,
                $request->data
            );
        } catch (\Illuminate\Database\QueryException $e) {
            return $this->errorUnprocessable('Unable to update field');
        }

        return $this->respondWithSuccess();
    }

    /**
     * Expire an order.
     *
     * @param ExpireRequest $request
     *
     * @return json
     */
    public function expire($id)
    {
        try {
            $result = app('api')->orders()->expire($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }

    /**
     * Set the shipping address of an order.
     *
     * @param string $id
     * @param StoreAddressRequest $request
     *
     * @return array
     */
    public function shippingAddress($id, StoreAddressRequest $request)
    {
        try {
            $order = app('api')->orders()->setShipping($id, $request->all(), $request->user());
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new OrderResource($order);
    }

    /**
     * Update an order.
     *
     * @param string $id
     * @param Request $request
     *
     * @return array
     */
    public function update($id, UpdateRequest $request)
    {
        try {
            $order = app('api')->orders()->update($id, $request->all(), $request->send_emails ?: false, $request->data);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new OrderResource($order);
    }

    /**
     * Get shipping methods for an order.
     *
     * @param string $orderId
     * @param Request $request
     *
     * @return array
     */
    public function shippingMethods($orderId, Request $request, ShippingMethodService $methods)
    {
        try {
            $options = $methods->getForOrder($orderId);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new ShippingPriceCollection($options);
    }

    /**
     * Add a contact to an order.
     *
     * @param string $orderId
     * @param Request $request
     *
     * @return array
     */
    public function addContact($orderId, Request $request)
    {
        try {
            $order = app('api')->orders()->setContact($orderId, $request->all());
            if ($request->meta) {
                $order->update([
                    'meta' => array_merge($order->meta ?? [], $request->meta ?? []),
                ]);
            }
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new OrderResource($order);
    }

    /**
     * Set an orders billing address.
     *
     * @param string $id
     * @param StoreAddressRequest $request
     *
     * @return array
     */
    public function billingAddress($id, StoreAddressRequest $request)
    {
        try {
            $order = app('api')->orders()->setBilling($id, $request->all());
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new OrderResource($order);
    }

    /**
     * Set shipping cost of an order.
     *
     * @param string $id
     * @param Request $request
     *
     * @return array
     */
    public function shippingCost(
        $id,
        AddShippingRequest $request,
        OrderFactoryInterface $factory,
        ShippingPriceService $prices,
        BasketFactoryInterface $basketFactory
    ) {
        $order = $this->orders->id($id)->first();
        $price = $prices->getByHashedId($request->price_id);
        $basket = $basketFactory->init($order->basket)->get();

        $order = $factory->order($order)
            ->basket($basket)
            ->include($request->includes ?? [])
            ->shipping($price, $request->preference)
            ->resolve();

        return new OrderResource($order);
    }

    /**
     * Get the invoice PDF.
     *
     * @param string $id
     * @param Request $request
     *
     * @return mixed
     */
    public function invoice($id, Request $request)
    {
        $order = app('api')->orders()->getByHashedId($id);
        $pdf = app('api')->orders()->getPdf($order);

        return new PdfResource($pdf);
    }

    /**
     * Handle the request to return an email preview.
     *
     * @param string $status
     * @param Request $request
     * @return void
     */
    public function emailPreview($status, Request $request)
    {
        // Get our mailer
        $mailer = config('getcandy.orders.mailers.'.$status);

        if (! $mailer) {
            return $this->errorUnprocessable([
                $status => 'No mailer exists',
            ]);
        }

        $order = app('api')->orders()->getByHashedId($request->id);

        // Instantiate the mailer.
        $mailerObject = new $mailer($order);

        foreach ($request->data ?? [] as $attribute => $value) {
            $mailerObject->with($attribute, $value);
        }

        $view = $mailerObject->render();

        return response()->json([
            'subject' => $mailerObject->subject,
            'content' => base64_encode($view),
        ]);
    }
}
