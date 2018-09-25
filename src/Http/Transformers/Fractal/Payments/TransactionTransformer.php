<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Payments;

use GetCandy\Api\Core\Payments\Models\Transaction;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Orders\OrderTransformer;

class TransactionTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'order',
    ];

    public function transform(Transaction $transaction)
    {
        return [
            'id' => $transaction->encodedId(),
            'transaction_id' => $transaction->transaction_id,
            'merchant' => $transaction->merchant,
            'amount' => $transaction->amount,
            'card_type' => $transaction->card_type,
            'last_four' => $transaction->last_four,
            'provider' => $transaction->provider,
            'driver' => $transaction->driver,
            'success' => (bool) $transaction->success,
            'refund' => (bool) $transaction->refund,
            'address_matched' => (bool) $transaction->address_matched,
            'cvc_matched' => (bool) $transaction->cvc_matched,
            'threed_secure' => (bool) $transaction->threed_secure,
            'postcode_matched' => (bool) $transaction->postcode_matched,
            'status' => $transaction->status,
            'notes' => $transaction->notes,
        ];
    }

    public function includeOrder(Transaction $transaction)
    {
        return $this->item($transaction->order, new OrderTransformer);
    }
}
