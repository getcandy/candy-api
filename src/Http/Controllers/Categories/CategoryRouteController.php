<?php

namespace GetCandy\Api\Http\Controllers\Categories;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Categories\Routes\CreateRequest;
use GetCandy\Api\Http\Resources\Routes\RouteResource;

class CategoryRouteController extends BaseController
{
    /**
     * @param  string  $category
     * @param  \GetCandy\Api\Http\Requests\Categories\Routes\CreateRequest  $request
     * @return \GetCandy\Api\Http\Resources\Routes\RouteResource
     */
    public function store($category, CreateRequest $request)
    {
        $result = GetCandy::categories()->createUrl($category, $request->all());

        return new RouteResource($result);
    }
}
