<?php

namespace GetCandy\Api\Core\Assets\Services;

use GetCandy\Api\Core\Assets\Models\AssetSource;
use GetCandy\Api\Core\Scaffold\BaseService;

class AssetSourceService extends BaseService
{
    public function __construct()
    {
        $this->model = new AssetSource;
    }
}
