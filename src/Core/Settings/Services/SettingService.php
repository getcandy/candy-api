<?php

namespace GetCandy\Api\Core\Settings\Services;

use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Settings\Models\Setting;

class SettingService extends BaseService
{
    public function __construct()
    {
        $this->model = new Setting;
    }

    public function get($handle)
    {
        $setting = $this->model->where('handle', '=', $handle)->firstOrFail();

        // If we're getting orders, load up the status info...
        $setting->config = collect(config('getcandy.' . $handle, []));

        return $setting;
    }
}
