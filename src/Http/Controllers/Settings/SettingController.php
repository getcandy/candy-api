<?php

namespace GetCandy\Api\Http\Controllers\Settings;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Resources\Settings\SettingCollection;
use GetCandy\Api\Http\Resources\Settings\SettingResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class SettingController extends BaseController
{
    public function index(Request $request)
    {
        return new SettingCollection(GetCandy::settings()->all());
    }

    /**
     * Handles the request to show a setting based on it's hashed ID.
     *
     * @param  string  $handle
     * @return array|\GetCandy\Api\Http\Resources\Settings\SettingResource
     */
    public function show($handle)
    {
        try {
            $setting = GetCandy::settings()->get($handle);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        if (! $setting) {
            return $this->errorNotFound();
        }

        return new SettingResource($setting);
    }
}
