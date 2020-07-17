<?php

namespace GetCandy\Api\Http\Controllers\Products;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Products\CreateUrlRequest;

class ProductRedirectController extends BaseController
{
    /**
     * @param  string  $product
     * @param  \GetCandy\Api\Http\Requests\Products\CreateUrlRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store($product, CreateUrlRequest $request)
    {
        GetCandy::products()->createUrl($product, $request->all());

        return $this->respondWithNoContent();
    }
}
