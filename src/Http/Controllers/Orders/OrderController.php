<?php

namespace GetCandy\Api\Http\Controllers\Orders;

use Illuminate\Http\Request;
use GetCandy\Api\Core\Orders\OrderSearchCriteria;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Orders\CreateRequest;
use GetCandy\Api\Http\Requests\Orders\UpdateRequest;
use GetCandy\Api\Http\Requests\Orders\ProcessRequest;
use GetCandy\Api\Http\Requests\Orders\BulkUpdateRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GetCandy\Api\Http\Requests\Orders\StoreAddressRequest;
use GetCandy\Api\Core\Orders\Exceptions\IncompleteOrderException;
use GetCandy\Api\Http\Transformers\Fractal\Orders\OrderTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Documents\PdfTransformer;
use GetCandy\Api\Core\Orders\Exceptions\BasketHasPlacedOrderException;
use GetCandy\Api\Core\Orders\Exceptions\OrderAlreadyProcessedException;
use GetCandy\Api\Core\Payments\Exceptions\ThreeDSecureRequiredException;
use GetCandy\Api\Http\Transformers\Fractal\Payments\ThreeDSecureTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Shipping\ShippingPriceTransformer;

class OrderController extends BaseController
{
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

        $criteria = new OrderSearchCriteria;

        $criteria->fill($request->all())
            ->set('without_scopes', [
                'open',
                'not_expired',
            ]);

        if ($request->user()->hasRole('admin') && ! $request->only_own) {
            $criteria->set('restrict', false);
        }
        $criteria->set('user', $request->user());

        $orders = $criteria->get();

        return $this->respondWithCollection($orders, new OrderTransformer);
    }

    public function getTypes(Request $request)
    {
        $types = app('api')->orders()->getTypes();

        return response()->json([
            'data' => $types,
        ]);
    }

    /**
     * Handles the request to show a channel based on it's hashed ID.
     * @param  string $id
     * @return Json
     */
    public function show($id)
    {
        try {
            $order = app('api')->orders()->getByHashedId($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($order, new OrderTransformer);
    }

    /**
     * Store either a new or existing basket.
     *
     * @param CreateRequest $request
     *
     * @return void
     */
    public function store(CreateRequest $request)
    {
        try {
            $order = app('api')->orders()->store($request->basket_id, $request->user());
        } catch (BasketHasPlacedOrderException $e) {
            return $this->errorForbidden(trans('getcandy::exceptions.basket_already_has_placed_order'));
        }

        return $this->respondWithItem($order->fresh(), new OrderTransformer);
    }

    /**
     * Process an order.
     *
     * @param ProcessRequest $request
     *
     * @return json
     */
    public function process(ProcessRequest $request)
    {
        try {
            $order = app('api')->orders()->process($request->all());
            if (! $order->placed_at) {
                return $this->errorForbidden('Payment has failed');
            }

            return $this->respondWithItem($order, new OrderTransformer);
        } catch (IncompleteOrderException $e) {
            return $this->errorForbidden('The order is missing billing information');
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        } catch (OrderAlreadyProcessedException $e) {
            return $this->errorUnprocessable('This order has already been processed');
        } catch (ThreeDSecureRequiredException $e) {
            return $this->respondWithItem($e->getResponse(), new ThreeDSecureTransformer);
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

        return $this->respondWithItem($order, new OrderTransformer);
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

        return $this->respondWithItem($order, new OrderTransformer);
    }

    /**
     * Get shipping methods for an order.
     *
     * @param string $orderId
     * @param Request $request
     *
     * @return array
     */
    public function shippingMethods($orderId, Request $request)
    {
        try {
            $options = app('api')->shippingMethods()->getForOrder($orderId);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithCollection($options, new ShippingPriceTransformer);
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
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($order, new OrderTransformer);
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

        return $this->respondWithItem($order, new OrderTransformer);
    }

    /**
     * Set shipping cost of an order.
     *
     * @param string $id
     * @param Request $request
     *
     * @return array
     */
    public function shippingCost($id, Request $request)
    {
        try {
            $order = app('api')->orders()->addShippingLine($id, $request->price_id, $request->preference);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($order, new OrderTransformer);
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

        return $this->respondWithItem($pdf, new PdfTransformer);
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
