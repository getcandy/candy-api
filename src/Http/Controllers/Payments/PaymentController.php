<?php

namespace GetCandy\Api\Http\Controllers\Payments;

use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Core\Payments\Models\Transaction;
use GetCandy\Api\Http\Requests\Payments\VoidRequest;
use GetCandy\Api\Http\Requests\Payments\RefundRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GetCandy\Api\Http\Requests\Payments\ValidateThreeDRequest;
use GetCandy\Api\Payments\Exceptions\AlreadyRefundedException;
use GetCandy\Api\Http\Transformers\Fractal\Orders\OrderTransformer;
use GetCandy\Api\Core\Payments\Exceptions\TransactionAmountException;
use GetCandy\Api\Http\Transformers\Fractal\Payments\ProviderTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Payments\TransactionTransformer;

class PaymentController extends BaseController
{
    public function provider()
    {
        $provider = app('api')->payments()->getProvider();

        return $this->respondWithItem($provider, new ProviderTransformer);
    }

    public function providers()
    {
        return app('api')->payments()->getProviders();
    }

    /**
     * Handle the request to refund a transaction.
     *
     * @param string $id
     * @param RefundRequest $request
     *
     * @return mixed
     */
    public function refund($id, RefundRequest $request)
    {
        try {
            $transaction = app('api')->payments()->refund(
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

        return $this->respondWithItem($transaction, new TransactionTransformer);
    }

    /**
     * Handle the request to void a payment.
     *
     * @param string $id
     * @param VoidRequest $request
     * @return Json
     */
    public function void($id, VoidRequest $request)
    {
        try {
            $transaction = app('api')->payments()->void($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        if (! $transaction->success) {
            return $this->errorWrongArgs($transaction->notes);
        }

        return $this->respondWithItem($transaction, new TransactionTransformer);
    }

    /**
     * Handles the request to validate a 3DSecure Transaction.
     *
     * @param ValidateThreeDRequest $request
     * @return void
     */
    public function validateThreeD(ValidateThreeDRequest $request)
    {
        try {
            $order = app('api')->orders()->getByHashedId($request->order_id);
            $response = app('api')->orders()->processThreeDSecure(
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

        return $this->respondWithItem($response, new OrderTransformer);
    }
}
