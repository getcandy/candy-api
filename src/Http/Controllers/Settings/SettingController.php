<?php

namespace GetCandy\Api\Http\Controllers\Settings;

use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Resources\Settings\SettingCollection;
use GetCandy\Api\Http\Resources\Settings\SettingResource;
use GetCandy\Api\Http\Transformers\Fractal\Settings\SettingTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class SettingController extends BaseController
{
    public function index(Request $request)
    {
        return new SettingCollection(app('api')->settings()->all());
    }

    /**
     * Handles the request to show a route based on it's hashed ID.
     * @param  string $slug
     * @return Json
     */
    public function show($handle)
    {
        try {
            $setting = app('api')->settings()->get($handle);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        if (! $setting) {
            return $this->errorNotFound();
        }

        return new SettingResource($setting);
    }
}
