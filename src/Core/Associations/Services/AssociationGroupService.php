<?php

namespace GetCandy\Api\Core\Associations\Services;

use GetCandy\Api\Core\Associations\Models\AssociationGroup;
use GetCandy\Api\Core\Scaffold\BaseService;

class AssociationGroupService extends BaseService
{
    public function __construct()
    {
        $this->model = new AssociationGroup;
    }
}
