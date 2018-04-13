<?php

namespace GetCandy\Api\Assets\Services;

use GetCandy\Api\Scaffold\BaseService;
use GetCandy\Api\Assets\Models\AssetSource;

class AssetSourceService extends BaseService
{
    public function __construct()
    {
        $this->model = new AssetSource;
    }
}
