<?php

namespace GetCandy\Api\Http\Resources\Payments;

use GetCandy\Api\Http\Resources\AbstractResource;

class PaymentProviderResource extends AbstractResource
{
    public function payload()
    {
        $data = [
            'name' => $this->resource->getName(),
        ];

        if (method_exists($this->resource, 'getClientToken')) {
            $data['client_token'] = $this->resource->getClientToken();
        }

        if (method_exists($this->resource, 'getTokenExpiry')) {
            $data['exires_at'] = $this->resource->getTokenExpiry();
        }

        return $data;
    }
}
