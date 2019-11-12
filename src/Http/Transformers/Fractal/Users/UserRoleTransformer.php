<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Users;

use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use Spatie\Permission\Models\Role;

class UserRoleTransformer extends BaseTransformer
{
    public function transform(Role $role)
    {
        return [
            'name' => $role->name,
            'guard' => $role->guard_name,
        ];
    }
}
