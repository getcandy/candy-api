<?php

namespace GetCandy\Api\Http\Controllers\Baskets;

use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Transformers\Fractal\Baskets\SavedBasketTransformer;
use Illuminate\Http\Request;

class SavedBasketController extends BaseController
{
    /**
     * Handle the request to update a saved basket.
     *
     * @param  string  $id
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function update($id, Request $request)
    {
        $basket = app('api')->savedBaskets()->update($id, $request->all());

        return $this->respondWithItem($basket, new SavedBasketTransformer);
    }
}
