<?php

namespace GetCandy\Api\Core\Search\Indexables;

use Carbon\Carbon;
use GetCandy;
use GetCandy\Api\Core\Scopes\ChannelScope;
use GetCandy\Api\Core\Scopes\CustomerGroupScope;
use GetCandy\Api\Core\Search\Indexable;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractIndexable
{
    protected $suffix = null;

    protected $model;

    protected $indexName;

    protected $data = [];

    protected $id;

    protected $index;

    public function __construct(Model $model = null, $id = null)
    {
        $this->model = $model;
        $this->id = $id;
        $this->set('id', $id);
    }

    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;

        return $this;
    }

    public function getSuffix()
    {
        return $this->suffix;
    }

    public function setIndexName($name)
    {
        $this->indexName = $name;

        return $this;
    }

    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * Gets a collection of indexables, based on a model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return mixed
     */
    public function getDocuments()
    {
        $attributes = $this->attributeMapping();

        $customerGroups = GetCandy::customerGroups()->all();

        $indexables = collect();

        foreach ($attributes as $attribute) {
            foreach ($attribute as $lang => $item) {
                $indice = "{$this->indexName}_{$lang}_{$this->suffix}";

                $this->setIndex($indice);

                $categories = $this->getCategories();

                $indexable->set('departments', $categories->toArray());
                $indexable->set('customer_groups', $this->getCustomerGroups());
                $indexable->set('channels', $this->getChannels());
                $indexable->set('breadcrumbs', $categories->implode('name', ' | '));

                $groupPricing = [];

                if (! empty($item['data'])) {
                    foreach ($item['data'] as $field => $value) {
                        $indexable->set($field, (count($value) > 1 ? $value : $value[0]));
                    }
                }

                if ($this->model->variants) {
                    $pricing = [];
                    foreach ($customerGroups as $customerGroup) {
                        $prices = [];
                        $i = 0;

                        foreach ($this->model->variants as $variant) {
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
                    foreach ($this->model->variants as $variant) {
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
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return array
     */
    public function attributeMapping()
    {
        $mapping = [];
        foreach ($this->model->attribute_data as $field => $channel) {
            foreach ($channel as $channelName => $locales) {
                foreach ($locales as $locale => $value) {
                    $newValue = $this->model->attribute($field, $channelName, $locale);
                    if (! is_array($newValue)) {
                        $newValue = strip_tags($newValue);
                    }
                    if (! $this->mappingValueExists($mapping, $this->model->id, $locale, $field, $newValue)) {
                        $mapping[$this->model->id][$locale]['data'][$field][] = $newValue;
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
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \Illuminate\Database\Eloquent\Collection
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
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \GetCandy\Api\Core\Search\Indexable
     */
    protected function getIndexable()
    {
        $indexable = new Indexable(
            $this->model->encoded_id
        );

        return $indexable;
    }

    /**
     * Gets the category mapping for an indexable.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $lang
     * @return \Illuminate\Database\Eloquent\Collection
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
        $groups = $model->customerGroups()->withoutGlobalScopes([
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

        return array_values($groups);
    }

    protected function getChannels(Model $model, $lang = 'en')
    {
        $channels = $model->channels->filter(function ($channel) {
            return $channel->published_at <= Carbon::now();
        })->map(function ($item) {
            return [
                'id' => $item->encodedId(),
                'handle' => $item->handle,
                'name' => $item->name,
            ];
        })->toArray();

        return array_values($channels);
    }

    public function getMapping()
    {
        $attributes = GetCandy::attributes()->all()->reject(function ($attribute) {
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
                        'type' => $attribute->type == 'number' ? 'integer' : 'keyword',
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

        return $attributes;
    }

    public function __get($attribute)
    {
        if (isset($this->data[$attribute])) {
            return $this->data[$attribute];
        }
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function setIndex($index)
    {
        $this->index = $index;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Adds an items to an array.
     *
     * @param  string  $key
     * @param  string  $value
     * @return $this
     */
    public function add($key, $value)
    {
        if (empty($this->data[$key])) {
            $this->set($key, $value);
        }
        $current = $this->data[$key];
        if (! is_array($current)) {
            $this->data[$key] = [];
        }
        array_push($this->data[$key], $value);

        return $this;
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }
}
