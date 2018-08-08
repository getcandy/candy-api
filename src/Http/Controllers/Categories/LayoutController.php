<?php

namespace GetCandy\Api\Http\Controllers\Categories;

use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Layouts\AttachRequest;
use GetCandy\Api\Http\Transformers\Fractal\Categories\CategoryTransformer;

class LayoutController extends BaseController
{
    /**
     * Handle the request to store a layout against a category.
     *
     * @param AttachRequest $request
     * @return json
     */
    public function store($category, AttachRequest $request)
    {
        $result = app('api')->categories()->updateLayout($category, $request->layout_id);

        return $this->respondWithItem($result, new CategoryTransformer);
    }
}
