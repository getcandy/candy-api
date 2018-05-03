<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Types;

use Carbon\Carbon;
use GetCandy\Api\Core\Search\Indexable;
use Illuminate\Database\Eloquent\Model;

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

                $indice = $this->getIndexName() . "_{$lang}_{$this->suffix}";

                $indexable->setIndex($indice);

                $indexable->set('image', $this->getThumbnail($model));

                $indexable->set('departments', $this->getCategories($model));
                $indexable->set('customer_groups', $this->getCustomerGroups($model));
                $indexable->set('channels', $this->getChannels($model));

                $groupPricing = [];

                if (! empty($item['data'])) {
                    foreach ($item['data'] as $field => $value) {
                        $indexable->set($field, $value);
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
        $searchable = $this->getIndexableAttributes($model);

        foreach ($model->attribute_data as $field => $channel) {
            if (! $searchable->contains($field)) {
                continue;
            }
            foreach ($channel as $channelName => $locales) {
                foreach ($locales as $locale => $value) {
                    $mapping[$model->id][$locale]['data'][$field] = strip_tags($model->attribute($field, $channelName, $locale));
                }
            }
        }

        return $mapping;
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
     * Gets the thumbnail for a model.
     *
     * @param Model $model
     * @return string
     */
    protected function getThumbnail(Model $model)
    {
        $url = null;
        if ($asset = $model->primaryAsset->first()) {
            $transform = $asset->first();
            $path = $transform->location.'/'.$transform->filename;
            $url = \Storage::disk($transform->disk)->url($path);
        }

        return $url;
    }

    /**
     * Gets the category mapping for an indexable.
     *
     * @param Model $model
     * @return array
     */
    protected function getCategories(Model $model, $lang = 'en')
    {
        $categories = $model->categories;

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
        })->toArray();
    }

    protected function getCustomerGroups(Model $model, $lang = 'en')
    {
        return $model->customerGroups->filter(function ($group) {
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
            return $attribute->system || !$attribute->searchable;
        })->mapWithKeys(function ($attribute) {
            switch ($attribute->type) {
                case 'radio':
                    $type = 'boolean';
                    break;
                case 'date':
                    $type = 'date';
                    break;
                default:
                    $type = 'text';
            }
            return [
                $attribute->handle => [
                    'type' => $type,
                    'analyzer' => 'standard',
                ]
            ];
        })->toArray();

        $attributes = array_merge($attributes, $this->mapping);

        return $attributes;
    }
}
