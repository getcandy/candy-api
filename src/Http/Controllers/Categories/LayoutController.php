<?php

namespace GetCandy\Api\Http\Controllers\Categories;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Layouts\AttachRequest;
use GetCandy\Api\Http\Resources\Categories\CategoryResource;

class LayoutController extends BaseController
{
    /**
     * Handle the request to store a layout against a category.
     *
     * @param  string  $category
     * @param  \GetCandy\Api\Http\Requests\Layouts\AttachRequest  $request
     * @return \GetCandy\Api\Http\Resources\Categories\CategoryResource
     */
    public function store($category, AttachRequest $request)
    {
        $result = GetCandy::categories()->updateLayout($category, $request->layout_id);

        return new CategoryResource($result);
    }
}
