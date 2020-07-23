<?php

namespace GetCandy\Api\Http\Controllers\Collections;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Collections\Products\UpdateRequest;
use GetCandy\Api\Http\Resources\Collections\CollectionResource;

class CollectionProductController extends BaseController
{
    /**
     * @param  string  $collection
     * @param  \GetCandy\Api\Http\Requests\Collections\Products\UpdateRequest  $request
     * @return array
     */
    public function store($collection, UpdateRequest $request)
    {
        $result = GetCandy::collections()->syncProducts($collection, $request->products);

        return new CollectionResource($result);
    }
}
