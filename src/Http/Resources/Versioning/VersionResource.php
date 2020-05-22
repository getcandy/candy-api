<?php

namespace GetCandy\Api\Http\Resources\Versioning;

use Hashids;
use GetCandy\Api\Http\Resources\Users\UserResource;
use GetCandy\Api\Http\Resources\AbstractResource;

class VersionResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => Hashids::encode($this->id),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function includes()
    {
        return [
            'user' => $this->include('user', UserResource::class),
        ];
    }
}
