<?php

namespace GetCandy\Api\Associations\Services;

use GetCandy\Api\Associations\Models\AssociationGroup;
use GetCandy\Api\Scaffold\BaseService;

class AssociationGroupService extends BaseService
{
    public function __construct()
    {
        $this->model = new AssociationGroup();
    }
}
