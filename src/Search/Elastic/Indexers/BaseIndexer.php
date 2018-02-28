<?php

namespace GetCandy\Api\Search\Elastic\Indexers;

use Illuminate\Database\Eloquent\Model;
use GetCandy\Api\Search\Indexable;
use Carbon\Carbon;

abstract class BaseIndexer
{
    public function getIndexName($lang = 'en')
    {
        return config('getcandy.search.index_prefix') . '_' . $this->type . '_' . $lang;
    }

    /**
     * Gets a collection of indexables, based on a model
     *
     * @param [type] $product
     * @return void
     */
    protected function getIndexables(Model $model)
    {
        $attributes = $this->attributeMapping($model);

        $indexables = collect();

        foreach ($attributes as $attribute) {
            foreach ($attribute as $lang => $item) {
                // Base Stuff
                $indexable = $this->getIndexable($model);
                $indexable->setIndex(
                    $this->getIndexName($lang)
                );

                $indexable->set('image', $this->getThumbnail($model));

                $indexable->set('departments', $this->getCategories($model));
                $indexable->set('customer_groups', $this->getCustomerGroups($model));
                $indexable->set('channels', $this->getChannels($model));


                if (!empty($item['data'])) {
                    foreach ($item['data'] as $field => $value) {
                        $indexable->set($field, $value);
                    }
                }

                if ($model->variants) {
                    $skus = [];
                    foreach ($model->variants as $variant) {
                        $skus[] = $variant->sku;
                        if (!$indexable->min_price || $indexable->min_price > $variant->price) {
                            $indexable->set('min_price', $variant->price);
                        }
                        if (!$indexable->max_price || $indexable->max_price < $variant->price) {
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
     * Gets the attribute mapping for a model to be indexed
     *
     * @param Model $model
     * @return Array
     */
    public function attributeMapping(Model $model)
    {
        $mapping = [];
        $searchable = $this->getIndexableAttributes($model);

        foreach ($model->attribute_data as $field => $channel) {
            if (!$searchable->contains($field)) {
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
     * Gets any attributes which are marked as searchable
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
     * Gets an indexable object
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
     * Gets the thumbnail for a model
     *
     * @param Model $model
     * @return String
     */
    protected function getThumbnail(Model $model)
    {
        $url = null;
        if (isset($model->primaryAsset()->thumbnail)) {
            $transform = $model->primaryAsset()->thumbnail->first();
            $path = $transform->location . '/' . $transform->filename;
            $url = \Storage::disk($model->primaryAsset()->disk)->url($path);
        }
        return $url;
    }

    /**
     * Gets the category mapping for an indexable
     *
     * @param Model $model
     * @return Array
     */
    protected function getCategories(Model $model, $lang = 'en')
    {
        $categories = $model->categories()->get();

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
                'name' => $item->attribute('name', null, $lang)
            ];
        })->toArray();
    }

    protected function getCustomerGroups(Model $model, $lang = 'en')
    {
        return $model->customerGroups()->where('visible', '=', true)->where('purchasable', '=', true)->get()->map(function ($item) use ($lang) {
            return [
                'id' => $item->encodedId(),
                'handle' => $item->handle,
                'name' => $item->name
            ];
        })->toArray();
    }

    protected function getChannels(Model $model, $lang = 'en')
    {
        return $model->channels()->whereDate('published_at', '<=', Carbon::now())->get()->map(function ($item) use ($lang) {
            return [
                'id' => $item->encodedId(),
                'handle' => $item->handle,
                'name' => $item->name
            ];
        })->toArray();
    }
}
