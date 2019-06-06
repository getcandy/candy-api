<?php

namespace GetCandy\Api\Core\Traits;

use Illuminate\Http\Response;
use GetCandy\Api\Http\Resources\Orders\OrderResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GetCandy\Api\Http\Resources\Payments\ThreeDSecureResource;
use GetCandy\Api\Core\Orders\Exceptions\PaymentFailedException;
use GetCandy\Api\Core\Orders\Exceptions\IncompleteOrderException;
use GetCandy\Api\Core\Orders\Exceptions\OrderAlreadyProcessedException;

trait CanProcessOrder
{
    /**
     * @param array $orderData
     *
     * @return OrderResource|ThreeDSecureResource|Response|array
     */
    public function processOrder(array $orderData)
    {
        try {
            return app('api')->orders()->process($orderData);
        } catch (PaymentFailedException $e) {
            return $this->errorForbidden('Payment has failed');
        } catch (IncompleteOrderException $e) {
            return $this->errorForbidden('The order is missing billing information');
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        } catch (OrderAlreadyProcessedException $e) {
            return $this->errorUnprocessable('This order has already been processed');
        }
    }
}
