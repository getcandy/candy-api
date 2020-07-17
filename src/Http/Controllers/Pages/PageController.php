<?php

namespace GetCandy\Api\Http\Controllers\Pages;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Resources\Pages\PageResource;
use GetCandy\Api\Http\Resources\Pages\PageCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PageController extends BaseController
{
    public function index()
    {
        $pages = GetCandy::pages()->getPaginatedData();
        return new PageCollection($pages);
    }

    /**
     * Handles the request to show a page based on it's hashed ID.
     *
     * @param  string  $channel
     * @return array
     */
    public function show($channel, $lang, $slug = null)
    {
        try {
            $currency = GetCandy::pages()->findPage($channel, $lang, $slug);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }
        return new PageResource($page);
    }
}
