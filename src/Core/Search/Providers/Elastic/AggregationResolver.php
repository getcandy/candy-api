<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic;

use GetCandy;
use GetCandy\Api\Http\Resources\Attributes\AttributeResource;
use GetCandy\Api\Http\Resources\Categories\CategoryResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AggregationResolver
{
    /**
     * Returns the attributes which have been aggregated.
     *
     * @param   array  $handles  Array of attribute handles
     *
     * @return  \Illuminate\Support\Collection
     */
    public function getAggregatedAttributes(array $handles)
    {
        return GetCandy::attributes()->getByHandles($handles);
    }

    /**
     * Returns categories which have been aggregated.
     *
     * @param   array  $buckets  The category aggregate bucket data
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAggregatedCategories(array $buckets)
    {
        return GetCandy::categories()->getByHashedIds(
            collect($buckets)->map(function ($cat) {
                return $cat['key'];
            })->toArray()
        );
    }

    /**
     * Resolve the search aggregations.
     *
     * @param   array  $aggregations
     *
     * @return  array                the resolved aggregations
     */
    public function resolve(array $aggregations)
    {
        $preAggs = collect($aggregations)->filter(function ($agg, $key) {
            return ! Str::contains($key, '_after');
        });
        $postAggs = collect($aggregations)->filter(function ($agg, $key) {
            return Str::contains($key, '_after');
        });

        return [
            'available' => $this->resolveAggregations($preAggs),
            'applied' => $this->resolveAggregations($postAggs),
        ];
    }
}
