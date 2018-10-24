<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Payments;

use GetCandy\Api\Core\Payments\Models\ReusablePayment;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class ReusablePaymentTransformer extends BaseTransformer
{
    public function transform(ReusablePayment $payment)
    {
        return [
            'id' => $payment->encodedId(),
            'type' => $payment->type,
            'provider' => $payment->provider,
            'last_four' => $payment->last_four,
            'token' => $payment->token,
            'expires_at' => $payment->expires_at,
        ];
    }
}
