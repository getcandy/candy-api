<?php

namespace GetCandy\Api\Http\Controllers\Layouts;

use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GetCandy\Api\Http\Transformers\Fractal\Layouts\LayoutTransformer;

class LayoutController extends BaseController
{
    public function index()
    {
        $pages = app('api')->layouts()->getPaginatedData();

        return $this->respondWithCollection($pages, new LayoutTransformer);
    }

    /**
     * Handles the request to show a layout based on it's hashed ID.
     * @param  string $id
     * @return Json
     */
    public function show($id)
    {
        try {
            $currency = app('api')->layouts()->getByEncodedId($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($currency, new PageTransformer);
    }
}
