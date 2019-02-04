<?php

namespace GetCandy\Api\Http\Resources\Users;

use GetCandy\Api\Http\Resources\AbstractResource;

class UserResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
        ];
    }

    public function includes()
    {
        return [
            // 'details' => $this->include('details', UserDetailsResource::class),
            'details' => $this->include('details', UserDetailsResource::class),
        ];
    }
}
