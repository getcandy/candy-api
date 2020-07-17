<?php

namespace GetCandy\Api\Http\Controllers\Payments;

use GetCandy;
use GetCandy\Api\Core\Payments\Exceptions\AlreadyRefundedException;
use GetCandy\Api\Core\Payments\Exceptions\TransactionAmountException;
use GetCandy\Api\Core\Payments\Models\Transaction;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Payments\RefundRequest;
use GetCandy\Api\Http\Requests\Payments\ValidateThreeDRequest;
use GetCandy\Api\Http\Requests\Payments\VoidRequest;
use GetCandy\Api\Http\Resources\Orders\OrderResource;
use GetCandy\Api\Http\Resources\Payments\PaymentProviderResource;
use GetCandy\Api\Http\Resources\Transactions\TransactionResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PaymentController extends BaseController
{
    public function provider()
    {
        return new PaymentProviderResource(
            GetCandy::payments()->getProvider()
        );
    }

    public function providers()
    {
        return GetCandy::payments()->getProviders();
    }

    /**
     * Handle the request to refund a transaction.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Payments\RefundRequest  $request
     * @return array|\GetCandy\Api\Http\Resources\Transactions\TransactionResource
     */
    public function refund($id, RefundRequest $request)
    {
        try {
            $transaction = GetCandy::payments()->refund(
                $id,
                $request->amount ?: null,
                $request->notes ?: null
            );
        } catch (AlreadyRefundedException $e) {
            return $this->errorWrongArgs('Refund already issued');
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        } catch (TransactionAmountException $e) {
            return $this->errorWrongArgs('Amount exceeds remaining balance');
        }

        if (! $transaction->success) {
            return $this->errorWrongArgs($transaction->status);
        }

        return new TransactionResource($transaction);
    }

    /**
     * Handle the request to void a payment.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Payments\VoidRequest  $request
     * @return array|\GetCandy\Api\Http\Resources\Transactions\TransactionResource
     */
    public function void($id, VoidRequest $request)
    {
        try {
            $transaction = GetCandy::payments()->void($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        if (! $transaction->success) {
            return $this->errorWrongArgs($transaction->notes);
        }

        return new TransactionResource($transaction);
    }

    /**
     * Handles the request to validate a 3DSecure Transaction.
     *
     * @param  \GetCandy\Api\Http\Requests\Payments\ValidateThreeDRequest  $request
     * @return array|\GetCandy\Api\Http\Resources\Orders\OrderResource
     */
    public function validateThreeD(ValidateThreeDRequest $request)
    {
        try {
            $order = GetCandy::orders()->getByHashedId($request->order_id);
            $response = GetCandy::orders()->processThreeDSecure(
                $order,
                $request->transaction,
                $request->paRes
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        if (! $response->placed_at) {
            return $this->errorWrongArgs();
        }

        return new OrderResource($response);
    }
}
