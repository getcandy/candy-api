<?php

namespace GetCandy\Api\Core\Search\Drivers\Elasticsearch\Actions\Searching;

use GetCandy;
use GetCandy\Api\Core\Attributes\Actions\FetchAttributes;
use GetCandy\Api\Http\Resources\Attributes\AttributeResource;
use GetCandy\Api\Http\Resources\Categories\CategoryResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Action;

class MapAggregations extends Action
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'aggregations' => 'array|min:0',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     *
     * @return array|null
     */
    public function handle()
    {
        $preAggs = collect($this->aggregations)->filter(function ($agg, $key) {
            return ! Str::contains($key, '_after');
        });
        $postAggs = collect($this->aggregations)->filter(function ($agg, $key) {
            return Str::contains($key, '_after');
        });

        return [
            'available' => $this->resolveAggregations($preAggs),
            'applied' => $this->resolveAggregations($postAggs),
        ];
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

    protected function resolveAggregations(Collection $aggregations)
    {
        // Get our attributes here so they're only fetched once
        $attributeHandles = $aggregations->mapWithKeys(function ($agg, $key) {
            return [str_replace('_after', '', $key) => null];
        })->keys()->all();

        $attributes = FetchAttributes::run([
            'handles' => $attributeHandles,
        ]);

        $categories = collect();

        if ($categoryBuckets = Arr::get($aggregations, 'categories.categories.buckets')) {
            dd($categoryBuckets);
            // Get the categories here so they're only fetched once
            $categories = $this->getAggregatedCategories($categoryBuckets);
        }

        return $aggregations->mapWithKeys(function ($agg, $key) use ($attributes, $categories) {
            $key = str_replace('_after', '', $key);

            $extra = [
                'handle' => $key,
            ];

            $data = $agg[$key] ?? $agg;

            if ($key == 'categories' || $key == 'category') {
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
                if ($attribute) {
                    $extra['attribute'] = new AttributeResource($attribute);
                }
            }

            return [$key => array_merge($extra, $data)];
        })->sortBy(function ($agg) {
            return ! empty($agg['attribute']) ? $agg['attribute']->position : 0;
        });
    }
}
