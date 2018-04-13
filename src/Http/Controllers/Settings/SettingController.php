<?php

namespace GetCandy\Api\Http\Controllers\Settings;

use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Transformers\Fractal\Search\SettingTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class SettingController extends BaseController
{
    /**
     * Handles the request to show a route based on it's hashed ID.
     *
     * @param string $slug
     *
     * @return Json
     */
    public function show($handle)
    {
        try {
            $setting = app('api')->settings()->get($handle);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($setting, new SettingTransformer());
    }
}
