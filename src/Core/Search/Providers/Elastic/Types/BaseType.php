<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Types;

use Carbon\Carbon;
use GetCandy\Api\Core\Search\Indexable;
use Illuminate\Database\Eloquent\Model;
use GetCandy\Api\Core\Scopes\ChannelScope;
use GetCandy\Api\Core\Scopes\CustomerGroupScope;

abstract class BaseType
{
    protected $suffix = null;

    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;

        return $this;
    }

    public function getSuffix()
    {
        return $this->suffix;
    }

    public function getHandle()
    {
        return $this->handle;
    }

    public function getIndexName()
    {
        return config('getcandy.search.index_prefix').'_'.$this->handle;
    }

    /**
     * Gets a collection of indexables, based on a model.
     *
     * @param [type] $product
     * @return void
     */
    protected function getIndexables(Model $model)
    {
        $attributes = $this->attributeMapping($model);

        $customerGroups = app('api')->customerGroups->all();

        $indexables = collect();

        foreach ($attributes as $attribute) {
            foreach ($attribute as $lang => $item) {
                // Base Stuff
                $indexable = $this->getIndexable($model);

                $indice = $this->getIndexName()."_{$lang}_{$this->suffix}";

                $indexable->setIndex($indice);

                $categories = $this->getCategories($model);

                $indexable->set('departments', $categories->toArray());
                $indexable->set('customer_groups', $this->getCustomerGroups($model));
                $indexable->set('channels', $this->getChannels($model));
                $indexable->set('breadcrumbs', $categories->implode('name', ' | '));

                $groupPricing = [];

                if (! empty($item['data'])) {
                    foreach ($item['data'] as $field => $value) {
                        $indexable->set($field, (count($value) > 1 ? $value : $value[0]));
                    }
                }

                if ($model->variants) {
                    $pricing = [];
                    foreach ($customerGroups as $customerGroup) {
                        $prices = [];
                        $i = 0;

                        foreach ($model->variants as $variant) {
                            $price = $variant->customerPricing->filter(function ($item) use ($customerGroup) {
                                return $customerGroup->id == $item->group->id;
                            })->first();

                            $prices[] = $price ? $price->price : $variant->price;
                            $i++;
                        }

                        if (! count($prices)) {
                            continue;
                        }

                        $pricing[] = [
                            'id' => $customerGroup->encodedId(),
                            'name' => $customerGroup->name,
                            'min' => min($prices),
                            'max' => max($prices),
                        ];
                    }

                    $indexable->set('pricing', $pricing);

                    $skus = [];
                    foreach ($model->variants as $variant) {
                        $skus[] = $variant->sku;
                        if (! $indexable->min_price || $indexable->min_price > $variant->price) {
                            $indexable->set('min_price', $variant->price);
                        }
                        if (! $indexable->max_price || $indexable->max_price < $variant->price) {
                            $indexable->set('max_price', $variant->price);
                        }
                    }
                    $indexable->set('sku', $skus);
                }

                $indexables->push($indexable);
            }
        }

        return $indexables;
    }

    /**
     * Gets the attribute mapping for a model to be indexed.
     *
     * @param Model $model
     * @return array
     */
    public function attributeMapping(Model $model)
    {
        $mapping = [];
        foreach ($model->attribute_data as $field => $channel) {
            foreach ($channel as $channelName => $locales) {
                foreach ($locales as $locale => $value) {
                    $newValue = $model->attribute($field, $channelName, $locale);
                    if (! is_array($newValue)) {
                        $newValue = strip_tags($newValue);
                    }
                    if (! $this->mappingValueExists($mapping, $model->id, $locale, $field, $newValue)) {
                        $mapping[$model->id][$locale]['data'][$field][] = $newValue;
                    }
                }
            }
        }

        return $mapping;
    }

    private function mappingValueExists($mapping, $id, $locale, $field, $value)
    {
        if (empty($mapping[$id][$locale]['data'][$field])) {
            return false;
        }

        if ($mapping[$id][$locale]['data'][$field][0] == $value) {
            return true;
        }

        return false;
    }

    /**
     * Gets any attributes which are marked as searchable.
     *
     * @param Model $model
     * @return void
     */
    protected function getIndexableAttributes(Model $model)
    {
        return $model->attributes()->whereSearchable(true)->get()->map(function ($attribute) {
            return $attribute->handle;
        });
    }

    /**
     * Gets an indexable object.
     *
     * @param array $attributes
     * @return Indexable
     */
    protected function getIndexable(Model $model)
    {
        $indexable = new Indexable(
            $model->encodedId()
        );

        return $indexable;
    }

    /**
     * Gets the category mapping for an indexable.
     *
     * @param Model $model
     * @return array
     */
    protected function getCategories(Model $model, $lang = 'en')
    {
        $categories = $model->categories()->withoutGlobalScopes([
            CustomerGroupScope::class,
            ChannelScope::class,
        ])->get();

        $cats = collect();

        foreach ($categories as $category) {
            $parent = $category->parent;
            while ($parent) {
                $categories->push($parent);
                $parent = $parent->parent;
            }
        }

        return $categories->map(function ($item) use ($lang) {
            return [
                'id' => $item->encodedId(),
                'name' => $item->attribute('name', null, $lang),
                'position' => $item->pivot->position ?? 1,
            ];
        });
    }

    protected function getCustomerGroups(Model $model, $lang = 'en')
    {
        return $model->customerGroups()->withoutGlobalScopes([
            CustomerGroupScope::class,
            ChannelScope::class,
        ])->get()->filter(function ($group) {
            return $group->pivot->purchasable && $group->pivot->visible;
        })->map(function ($item) {
            return [
                'id' => $item->encodedId(),
                'handle' => $item->handle,
                'name' => $item->name,
            ];
        })->toArray();
    }

    protected function getChannels(Model $model, $lang = 'en')
    {
        return $model->channels->filter(function ($channel) {
            return $channel->published_at <= Carbon::now();
        })->map(function ($item) use ($lang) {
            return [
                'id' => $item->encodedId(),
                'handle' => $item->handle,
                'name' => $item->name,
            ];
        })->toArray();
    }

    public function getMapping()
    {
        $attributes = app('api')->attributes()->all()->reject(function ($attribute) {
            return $attribute->system;
        })->mapWithKeys(function ($attribute) {
            $payload = [];

            if (! $attribute->searchable && ! $attribute->filterable) {
                $payload[$attribute->handle]['enabled'] = false;
            } else {
                if ($attribute->type == 'number') {
                    $data = ['type' => 'integer'];
                } else {
                    $data = [
                        'type' => 'text',
                        'analyzer' => 'standard',
                    ];
                }
                $payload[$attribute->handle] = $data;

                $payload[$attribute->handle]['fields'] = [
                    'sortable' => [
                        'type' => 'keyword',
                    ],
                ];

                if ($attribute->filterable) {
                    $payload[$attribute->handle]['fields']['filter'] = [
                        'type' => $attribute->type == 'number' ? 'integer' : 'keyword',
                    ];
                }
            }

            return $payload;
        })->toArray();

        return array_merge($attributes, $this->mapping);
    }
}
