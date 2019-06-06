<?php

namespace GetCandy\Api\Core\Traits;

use GetCandy\Api\Core\Payments\Models\PaymentType;
use GetCandy\Api\Http\Resources\Orders\OrderResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GetCandy\Api\Core\Payments\Services\PaymentTypeService;
use GetCandy\Api\Http\Resources\Payments\ThreeDSecureResource;
use GetCandy\Api\Core\Orders\Interfaces\OrderCriteriaInterface;
use GetCandy\Api\Core\Orders\Exceptions\IncompleteOrderException;
use GetCandy\Api\Core\Orders\Exceptions\OrderAlreadyProcessedException;
use GetCandy\Api\Core\Orders\Interfaces\OrderProcessingFactoryInterface;
use GetCandy\Api\Core\Payments\Exceptions\ThreeDSecureRequiredException;

trait CanProcessOrder
{
    /**
     * @param int $orderId
     * @param array $orderData
     *
     * @return OrderResource|ThreeDSecureResource|Response
     */
    public function processOrder(int $orderId, array $orderData)
    {
        $factory = app(OrderProcessingFactoryInterface::class);
        $criteria = app(OrderCriteriaInterface::class);

        try {
            $paymentType = $this->getPaymentType($orderData);

            $order = $criteria->id($orderId)->first();

            if (! $order) {
                if ($this->isOrderAlreadyProcessed($orderId)) {
                    throw new OrderAlreadyProcessedException;
                }
            }

            $order = $factory
                ->order($order)
                ->provider($paymentType)
                ->nonce($orderData['payment_token'])
                ->type($orderData['type'])
                ->customerReference($orderData['customer_reference'])
                ->meta($orderData['meta'] ?? [])
                ->notes($orderData['notes'])
                ->payload($orderData['data'] ?: [])
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

    /**
     * The order type may be passed by ID or by handle, so this method
     * identifies which, and then calls the correct method to retrieve it.
     *
     * @param array $orderData
     *
     * @return PaymentType|null
     */
    private function getPaymentType(array $orderData): ?PaymentType
    {
        $paymentTypes = app(PaymentTypeService::class);

        if ($orderData['payment_type_id']) {
            return $paymentTypes->getByHashedId($orderData['payment_type_id']);
        }

        if ($orderData['payment_type']) {
            return $paymentTypes->getByHandle($orderData['payment_type']);
        }

        return null;
    }

    private function isOrderAlreadyProcessed(int $orderId): bool
    {
        $criteria = app(OrderCriteriaInterface::class);

        $placedOrder = $criteria->id($orderId)->getBuilder()->withoutGlobalScopes()->first();
        if ($placedOrder && $placedOrder->placed_at) {
            return true;
        }

        return false;
    }
}
