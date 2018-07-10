<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Auth;

use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class PasswordTokenTransformer extends BaseTransformer
{
    public function transform($token)
    {
        return [
            'token' => $token,
        ];
    }
}
