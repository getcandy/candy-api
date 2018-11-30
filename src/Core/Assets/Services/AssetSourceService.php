<?php

namespace GetCandy\Api\Core\Assets\Services;

use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Assets\Models\AssetSource;

class AssetSourceService extends BaseService
{
    public function __construct()
    {
        $this->model = new AssetSource;
    }
}
