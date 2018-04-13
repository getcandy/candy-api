<?php

namespace GetCandy\Api\Http\Controllers\Countries;

use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Transformers\Fractal\Countries\CountryGroupTransformer;
use Illuminate\Http\Request;

class CountryController extends BaseController
{
    /**
     * Returns a listing of channels.
     *
     * @return Json
     */
    public function index(Request $request)
    {
        $collection = app('api')->countries()->getGroupedByRegion();

        return $this->respondWithCollection($collection, new CountryGroupTransformer());
    }
}
