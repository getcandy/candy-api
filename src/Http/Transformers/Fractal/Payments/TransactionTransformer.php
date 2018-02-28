<?php
namespace GetCandy\Api\Http\Transformers\Fractal\Payments;

use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Orders\OrderTransformer;
use GetCandy\Api\Payments\Providers\AbstractProvider;
use GetCandy\Api\Payments\Models\Transaction;

class TransactionTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'order'
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
            'success' => (bool) $transaction->success,
            'status' => $transaction->status,
            'notes' => $transaction->notes
        ];
    }

    public function includeOrder(Transaction $transaction)
    {
        return $this->item($transaction->order, new OrderTransformer);
    }
}
