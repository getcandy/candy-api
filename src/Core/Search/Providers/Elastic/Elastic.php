<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic;

use GetCandy\Api\Core\Search\Providers\Elastic\Types\ProductType;
use GetCandy\Api\Core\Search\SearchContract;
use GetCandy\Api\Http\Resources\Attributes\AttributeResource;
use GetCandy\Api\Http\Resources\Categories\CategoryResource;
use Illuminate\Support\Arr;

class Elastic implements SearchContract
{
    /**
     * @var \GetCandy\Api\Core\Search\Providers\Elastic\Search
     */
    protected $client;

    /**
     * @var \GetCandy\Api\Core\Search\Providers\Elastic\Indexer
     */
    protected $indexer;

    public function __construct(Search $client, Indexer $indexer)
    {
        $this->client = $client;
        $this->indexer = $indexer;
    }

    public function indexer()
    {
        return $this->indexer;
    }

    public function client()
    {
        return $this->client;
    }

    public function products()
    {
        return app()->make(ProductType::class);
    }

    public function parseAggregations(array $aggregations)
    {
        // Get our attributes.
        $attributes = app('api')->attributes()->getByHandles(array_keys($aggregations));

        $categoryBuckets = Arr::get($aggregations, 'categories.categories.buckets');

        $categoryIds = collect($categoryBuckets)->map(function ($cat) {
            return $cat['key'];
        });

        $categories = app('api')->categories()->getByHashedIds($categoryIds->toArray());

        return collect($aggregations)->map(function ($agg, $key) use ($attributes, $categories) {
            $extra = [
                'handle' => $key,
            ];

            if ($key == 'categories') {
                foreach ($agg['categories']['buckets'] as $bucketIndex => $bucket) {
                    $category = $categories->first(function ($cat) use ($bucket) {
                        return $cat->encodedId() == $bucket['key'];
                    });

                    if ($category) {
                        $agg['categories']['buckets'][$bucketIndex]['data'] = new CategoryResource($category);
                    } else {
                        unset($agg['categories']['buckets'][$bucketIndex]);
                    }
                }
            } else {
                $attribute = $attributes->first(function ($att) use ($key) {
                    return $att->handle == $key;
                });
                $extra['data'] = new AttributeResource($attribute);
            }

            return array_merge($extra, $agg[$key]);
        })->sortBy(function ($agg) {
            return ! empty($agg['data']) ? $agg['data']->resource->position : 0;
        });
    }
}
