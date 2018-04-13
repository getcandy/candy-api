<?php

namespace GetCandy\Api\Layouts\Services;

use GetCandy\Api\Layouts\Models\Layout;
use GetCandy\Api\Scaffold\BaseService;

class LayoutService extends BaseService
{
    public function __construct()
    {
        $this->model = new Layout();
    }
}
