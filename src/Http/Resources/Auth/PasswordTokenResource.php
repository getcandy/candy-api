<?php

namespace GetCandy\Api\Http\Resources\Auth;

use GetCandy\Api\Http\Resources\AbstractResource;

class PasswordTokenResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->token,
        ];
    }
}
