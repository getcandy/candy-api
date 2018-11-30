<?php

namespace GetCandy\Api\Http\Controllers\Collections;

use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Collections\Routes\CreateRequest;

class CollectionRouteController extends BaseController
{
    /**
     * @param                                                       $product
     * @param \GetCandy\Api\Http\Requests\Products\CreateUrlRequest $request
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function store($collection, CreateRequest $request)
    {
        $result = app('api')->collections()->createUrl($collection, $request->all());

        return $this->respondWithNoContent();
    }
}
