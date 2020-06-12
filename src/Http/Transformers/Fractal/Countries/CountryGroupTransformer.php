<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Countries;

use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class CountryGroupTransformer extends BaseTransformer
{
    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = [
        'countries',
    ];

    public function transform($collection)
    {
        $data = [
            'region' => $collection->first()->region ?: 'Rest of world',
        ];

        return $data;
    }

    public function includeCountries($collection)
    {
        return $this->collection($collection, new CountryTransformer);
    }
}
