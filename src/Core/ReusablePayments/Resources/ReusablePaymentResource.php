<?php

namespace GetCandy\Api\Core\ReusablePayments\Resources;

use Carbon\Carbon;
use GetCandy\Api\Http\Resources\AbstractResource;

class ReusablePaymentResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'user_id' => $this->user_id, // check if this should be the encoded one?
            'type' => $this->type,
            'provider' => $this->provider,
            'last_four' => $this->last_four,
            'token' => $this->token, // may not want token here
            'expires_at' => Carbon::parse($this->expires_at)->toIso8601String(),
        ];
    }
}
