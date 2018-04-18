<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Countries;

use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class CountryGroupTransformer extends BaseTransformer
{
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
