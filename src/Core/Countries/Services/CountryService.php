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
     * Get countries grouped by region.
     *
     * @return Collection
     */
    public function getGroupedByRegion()
    {
        $countries = $this->model->get();

        $countries = $countries->sort(function ($a, $b) {
            return strcmp($a->name, $b->name);
        })->groupBy('region');

        return $countries;
    }

    /**
     * Get a country by its name.
     *
     * @param string $name
     * @param string $locale
     * @return Country
     */
    public function getByName($name)
    {
        return $this->model->where('name', $name)->first();
    }
}
