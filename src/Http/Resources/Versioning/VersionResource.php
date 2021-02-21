<?php

namespace GetCandy\Api\Http\Resources\Versioning;

use Hashids;
use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Core\Users\Resources\UserResource;
use GetCandy\Api\Http\Resources\Versioning\VersionCollection;

class VersionResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => Hashids::encode($this->id),
            'model_data' => $this->model_data,
            'versionable_type' => class_basename($this->versionable_type),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function includes()
    {
        return [
            'user' => $this->include('user', UserResource::class),
            'relations' => new VersionCollection($this->whenLoaded('relations'))
        ];
    }
}
