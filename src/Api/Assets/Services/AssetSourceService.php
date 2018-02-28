<?php

namespace GetCandy\Api\Assets\Services;

use GetCandy\Api\Assets\Models\AssetSource;
use GetCandy\Api\Scaffold\BaseService;

class AssetSourceService extends BaseService
{
    public function __construct()
    {
        $this->model = new AssetSource;
    }
}