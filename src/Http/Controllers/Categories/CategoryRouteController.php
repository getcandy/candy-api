<?php

namespace GetCandy\Api\Http\Controllers\Categories;

use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Categories\Routes\CreateRequest;

class CategoryRouteController extends BaseController
{
    /**
     * @param                                                       $product
     * @param \GetCandy\Api\Http\Requests\Products\CreateUrlRequest $request
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function store($category, CreateRequest $request)
    {
        $result = app('api')->categories()->createUrl($category, $request->all());

        return $this->respondWithNoContent();
    }
}
