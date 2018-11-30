<?php

namespace GetCandy\Api\Http\Controllers\Collections;

use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Collections\Products\UpdateRequest;
use GetCandy\Api\Http\Transformers\Fractal\Collections\CollectionTransformer;

class CollectionProductController extends BaseController
{
    /**
     * @param                                                       $product
     * @param \GetCandy\Api\Http\Requests\Products\CreateUrlRequest $request
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function store($collection, UpdateRequest $request)
    {
        $result = app('api')->collections()->syncProducts($collection, $request->products);

        return $this->respondWithItem($result, new CollectionTransformer);
    }
}
