<?php

namespace GetCandy\Api\Settings\Services;

use GetCandy\Api\Scaffold\BaseService;
use GetCandy\Api\Settings\Models\Setting;

class SettingService extends BaseService
{
    public function __construct()
    {
        $this->model = new Setting;
    }

    public function get($handle)
    {
        return $this->model->where('handle', '=', $handle)->first();
    }
}