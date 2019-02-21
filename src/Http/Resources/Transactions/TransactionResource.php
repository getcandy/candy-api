<?php

namespace GetCandy\Api\Http\Resources\Transactions;

use GetCandy\Api\Http\Resources\AbstractResource;

class TransactionResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'transaction_id' => $this->transaction_id,
            'merchant' => $this->merchant,
            'amount' => $this->amount,
            'card_type' => $this->card_type,
            'last_four' => $this->last_four,
            'provider' => $this->provider,
            'driver' => $this->driver,
            'success' => (bool) $this->success,
            'refund' => (bool) $this->refund,
            'address_matched' => (bool) $this->address_matched,
            'cvc_matched' => (bool) $this->cvc_matched,
            'threed_secure' => (bool) $this->threed_secure,
            'postcode_matched' => (bool) $this->postcode_matched,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
        ];
    }
}
