<?php

namespace GetCandy\Api\Http\Controllers\Baskets;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Resources\Baskets\SavedBasketResource;
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
        return new SavedBasketResource(
            GetCandy::savedBaskets()->update($id, $request->all())
        );
    }
}
