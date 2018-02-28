<?php

namespace GetCandy\Api\Auth\Services;

class RoleService
{
    public function getHubAccessRoles()
    {
        return array_merge(config('getcandy.hub_access', []), [
            'admin'
        ]);
    }
}
