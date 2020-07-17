<?php

namespace GetCandy\Api\Http\Controllers\Pages;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Transformers\Fractal\Pages\PageTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PageController extends BaseController
{
    public function index()
    {
        $pages = GetCandy::pages()->getPaginatedData();

        return $this->respondWithCollection($pages, new PageTransformer);
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

        return $this->respondWithItem($currency, new PageTransformer);
    }
}
