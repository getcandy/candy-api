<?php
namespace GetCandy\Api\Countries\Services;

use GetCandy\Api\Scaffold\BaseService;
use GetCandy\Api\Countries\Models\Country;

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
