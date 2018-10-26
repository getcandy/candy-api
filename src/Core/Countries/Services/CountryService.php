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

    /**
     * Get a collection of Countries grouped by region.
     *
     * @return  \Illuminate\Support\Collection
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

    /**
     * Get a country by its name.
     *
     * @param   string  $name
     * @param   string  $locale
     * @return  Country
     */
    public function getByName($name, $locale = 'en')
    {
        return $this->model->where('name->'.$locale, $name)->first();
    }
}
