<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Payments;

use GetCandy\Api\Payments\Providers\AbstractProvider;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class ProviderTransformer extends BaseTransformer
{
    public function transform(AbstractProvider $provider)
    {
        $data = [
            'name' => $provider->getName(),
        ];

        if (method_exists($provider, 'getClientToken')) {
            $data['client_token'] = $provider->getClientToken();
        }

        return $data;
    }
}
