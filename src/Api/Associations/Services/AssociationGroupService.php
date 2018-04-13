<?php

namespace GetCandy\Api\Associations\Services;

use GetCandy\Api\Scaffold\BaseService;
use GetCandy\Api\Associations\Models\AssociationGroup;

class AssociationGroupService extends BaseService
{
    public function __construct()
    {
        $this->model = new AssociationGroup;
    }
}
