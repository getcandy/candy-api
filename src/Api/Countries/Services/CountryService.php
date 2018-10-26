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

    /**
     * Get a collection of Countries grouped by region
     *
     *
     * @return \Illuminate\Support\Collection
     */
    public function getGroupedByRegion()
    {
        return $this->model
            ->get()
            ->sort(function ($a, $b) {
                return strcmp(
                    $a->translation('name'),
                    $b->translation('name')
                );
            })
            ->groupBy('region');
    }
}
