<?php

namespace GetCandy\Api\Core\Countries\Services;

use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Countries\Models\Country;

class CountryService extends BaseService
{
    public function __construct()
    {
        $this->model = new Country;
    }

    public function getGroupedByRegion()
    {
        $countries = $this->model->get();

        $countries = $countries->sort(function ($a, $b) {
            return strcmp($a->translation('name'), $b->translation('name'));
        })->groupBy('region');

        return $countries;
    }
}
