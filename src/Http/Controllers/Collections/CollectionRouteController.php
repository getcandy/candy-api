<?php

namespace GetCandy\Api\Http\Controllers\Collections;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Collections\Routes\CreateRequest;

class CollectionRouteController extends BaseController
{
    /**
     * @param  string  $collection
     * @param  \GetCandy\Api\Http\Requests\Collections\Routes\CreateRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store($collection, CreateRequest $request)
    {
        GetCandy::collections()->createUrl($collection, $request->all());

        return $this->respondWithNoContent();
    }
}
