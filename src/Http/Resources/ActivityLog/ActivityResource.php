<?php

namespace GetCandy\Api\Http\Resources\ActivityLog;

use GetCandy\Api\Core\Users\Resources\UserResource;
use GetCandy\Api\Http\Resources\AbstractResource;

class ActivityResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->id,
            'type' => $this->log_name,
            'description' => $this->description,
            'properties' => $this->properties,
            'created_at' => $this->created_at,
        ];
    }

    public function includes()
    {
        return [
            'user' => $this->include('causer', UserResource::class),
        ];
    }
}
