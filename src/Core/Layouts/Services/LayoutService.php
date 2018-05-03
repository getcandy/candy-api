<?php

namespace GetCandy\Api\Core\Layouts\Services;

use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Layouts\Models\Layout;

class LayoutService extends BaseService
{
    public function __construct()
    {
        $this->model = new Layout();
    }
}
