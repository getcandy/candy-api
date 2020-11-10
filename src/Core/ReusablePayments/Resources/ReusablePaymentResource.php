<?php

namespace GetCandy\Api\Core\ReusablePayments\Resources;

use Carbon\Carbon;
use GetCandy\Api\Core\Users\Resources\UserResource;
use GetCandy\Api\Http\Resources\AbstractResource;

class ReusablePaymentResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'type' => $this->type,
            'provider' => $this->provider,
            'last_four' => $this->last_four,
            'token' => $this->token,
            'expires_at' => Carbon::parse($this->expires_at)->toIso8601String(),
        ];
    }

    public function includes()
    {
        return [
            'user' => $this->include('user', UserResource::class),
        ];
    }
}
