<?php

namespace GetCandy\Api\Http\Controllers\Countries;

use GetCandy;
use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Resources\Countries\CountryGroupCollection;

class CountryController extends BaseController
{
    /**
     * Returns a listing of countries.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function index(Request $request)
    {
        $countries = GetCandy::countries()->getGroupedByRegion();
        return new CountryGroupCollection($countries);
    }
}
