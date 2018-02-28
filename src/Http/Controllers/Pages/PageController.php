<?php

namespace GetCandy\Api\Http\Controllers\Pages;

use GetCandy\Api\Exceptions\MinimumRecordRequiredException;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Transformers\Fractal\Pages\PageTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageController extends BaseController
{
    public function index()
    {
        $pages = app('api')->pages()->getPaginatedData();
        return $this->respondWithCollection($pages, new PageTransformer);
    }
    /**
     * Handles the request to show a currency based on it's hashed ID
     * @param  String $id
     * @return Json
     */
    public function show($channel, $lang, $slug = null)
    {
        try {
            $currency = app('api')->pages()->findPage($channel, $lang, $slug);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }
        return $this->respondWithItem($currency, new PageTransformer);
    }
}
