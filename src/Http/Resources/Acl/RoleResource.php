<?php

namespace GetCandy\Api\Http\Resources\Acl;

use GetCandy\Api\Http\Resources\AbstractResource;

class RoleResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'guard_name' => $this->guard_name
        ];
    }

    public function includes()
    {
        return [
            'permissions' => new PermissionCollection($this->whenLoaded('permissions')),
        ];
    }
}
