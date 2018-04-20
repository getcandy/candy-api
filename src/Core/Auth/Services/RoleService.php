<?php

namespace GetCandy\Api\Core\Auth\Services;

class RoleService
{
    public function getHubAccessRoles()
    {
        return array_merge(config('getcandy.hub_access', []), [
            'admin',
        ]);
    }
}
