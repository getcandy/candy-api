<?php

namespace GetCandy\Api\Http\Controllers\Baskets;

use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Transformers\Fractal\Baskets\SavedBasketTransformer;

class SavedBasketController extends BaseController
{
    /**
     * Handle the request to update a saved basket.
     *
     * @param string $id
     * @param Request $request
     * @return void
     */
    public function update($id, Request $request)
    {
        $basket = app('api')->savedBaskets()->update($id, $request->all());

        return $this->respondWithItem($basket, new SavedBasketTransformer);
    }
}
