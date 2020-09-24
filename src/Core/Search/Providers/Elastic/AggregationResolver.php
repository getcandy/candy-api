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

    protected function resolveAggregations(Collection $aggregations)
    {
        // Get our attributes here so they're only fetched once
        $attributes = $this->getAggregatedAttributes(
            $aggregations->mapWithKeys(function ($agg, $key) {
                return [str_replace('_after', '', $key) => null];
            })->keys()->all()
        );

        $categories = collect();

        if ($categoryBuckets = Arr::get($aggregations, 'categories.categories.buckets')) {
            // Get the categories here so they're only fetched once
            $categories = $this->getAggregatedCategories($categoryBuckets);
        }

        return $aggregations->mapWithKeys(function ($agg, $key) use ($attributes, $categories) {
            $key = str_replace('_after', '', $key);

            $extra = [
                'handle' => $key,
            ];

            $data = $agg[$key] ?? $agg;

            if ($key == 'categories') {
                foreach ($data['buckets'] as $bucketIndex => $bucket) {
                    $category = $categories->first(function ($cat) use ($bucket) {
                        return $cat->encodedId() == $bucket['key'];
                    });

                    if ($category) {
                        $data['buckets'][$bucketIndex]['data'] = new CategoryResource($category);
                    } else {
                        unset($data['buckets'][$bucketIndex]);
                    }
                }
            } else {
                $attribute = $attributes->first(function ($att) use ($key) {
                    return $att->handle == $key;
                });
                $extra['data'] = new AttributeResource($attribute);
            }

            return [$key =>  array_merge($extra, $data)];
        })->sortBy(function ($agg) {
            return ! empty($agg['data']) ? $agg['data']->resource->position : 0;
        });
    }
}
