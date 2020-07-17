<?php

namespace GetCandy\Api\Http\Controllers\Products;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Products\CreateUrlRequest;
use GetCandy\Api\Http\Requests\Products\UpdateUrlsRequest;
use GetCandy\Api\Http\Resources\Routes\RouteResource;

class ProductRouteController extends BaseController
{
    /**
     * @param  string  $product
     * @param  \GetCandy\Api\Http\Requests\Products\CreateUrlRequest  $request
     * @return \GetCandy\Api\Http\Resources\Routes\RouteResource
     */
    public function store($product, CreateUrlRequest $request)
    {
        $result = GetCandy::products()->createUrl($product, $request->all());

        return new RouteResource($result);
    }

    public function update($product, UpdateUrlsRequest $request)
    {
        GetCandy::products()->saveUrls($product, $request->urls);

        return $this->respondWithNoContent();
    }
}
