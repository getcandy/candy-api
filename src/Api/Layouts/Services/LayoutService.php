<?php

namespace GetCandy\Api\Layouts\Services;

use GetCandy\Api\Scaffold\BaseService;
use GetCandy\Api\Layouts\Models\Layout;

class LayoutService extends BaseService
{
    public function __construct()
    {
        $this->model = new Layout();
    }
}
