<?php

namespace GetCandy\Api\Http\Controllers\Layouts;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Plugins\PageBuilder\Http\Resources\LayoutCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LayoutController extends BaseController
{
    public function index()
    {
        return new LayoutCollection(
            GetCandy::layouts()->getPaginatedData()
        );
    }

    /**
     * Handles the request to show a layout based on it's hashed ID.
     *
     * @param  string  $id
     * @return array
     */
    public function show($id)
    {
        try {
            $layout = GetCandy::layouts()->getByHashedId($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new LayoutResource($layout);
    }
}
