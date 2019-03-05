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
        $setting = $this->model->where('handle', '=', $handle)->first();

        // If we're getting orders, load up the status info...
        if ($setting && $handle == 'orders') {
            $setting->config = collect(config('getcandy.orders', []));
        }

        return $setting;
    }
}
