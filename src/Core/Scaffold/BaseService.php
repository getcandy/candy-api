<?php

namespace GetCandy\Api\Core\Scaffold;

use Carbon\Carbon;
use GetCandy;
use GetCandy\Api\Core\Attributes\Events\AttributableSavedEvent;
use GetCandy\Api\Core\Channels\Actions\FetchChannel;
use GetCandy\Api\Core\Routes\Actions\SearchForRoute;
use Illuminate\Database\Eloquent\Model;

abstract class BaseService
{
    protected $with = [];

    public function getModelName()
    {
        return get_class($this->model);
    }

    public function with(array $data)
    {
        $this->with = $data;

        return $this;
    }

    /**
     * Returns model by a given hashed id.
     *
     * @param  string  $id
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getByHashedId($id)
    {
        $id = $this->model->decodeId($id);

        return $this->model->withoutGlobalScopes()->findOrFail($id);
    }

    /**
     * Get a collection of models from given Hashed IDs.
     *
     * @param  array  $ids
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByHashedIds(array $ids)
    {
        $parsedIds = [];
        foreach ($ids as $hash) {
            $parsedIds[] = $this->model->decodeId($hash);
        }

        return $this->model->withoutGlobalScopes()->with($this->with)->find($parsedIds);
    }

    /**
     * Returns the record count for the model.
     *
     * @return int
     */
    public function count()
    {
        return (bool) $this->model->count();
    }

    public function all()
    {
        return $this->model->get();
    }

    /**
     * Gets the decoded id for the model.
     *
     * @param  string  $hash
     * @return int
     */
    public function getDecodedId($hash)
    {
        return $this->model->decodeId($hash);
    }

    public function getEncodedId($id)
    {
        return $this->model->encode($id);
    }

    public function getById($id)
    {
        return $this->model->find($id);
    }

    public function getDecodedIds(array $ids)
    {
        $decoded = [];
        foreach ($ids as $id) {
            $decoded[] = $this->getDecodedId($id);
        }

        return $decoded;
    }

    public function getEncodedIds(array $ids)
    {
        $encoded = [];
        foreach ($ids as $id) {
            $encoded[] = $this->getEncodedId($id);
        }

        return $encoded;
    }

    /**
     * Returns the record considered the default.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getDefaultRecord()
    {
        return $this->model->default()->first();
    }

    /**
     * Get a record by it's handle.
     *
     * @param  string  $handle
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getByHandle($handle)
    {
        return $this->model->where('handle', '=', $handle)->first();
    }

    /**
     * Gets paginated data for the record.
     *
     * @param  int  $length
     * @param  int|null  $page
     * @param  array|string|null  $relations
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPaginatedData($length = 50, $page = null, $relations = null)
    {
        $query = $this->model->orderBy('created_at', 'desc');

        if ($relations) {
            $query->with($relations);
        }

        return $query->paginate($length, ['*'], 'page', $page);
    }

    /**
     * Gets a new suggested default model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getNewSuggestedDefault()
    {
        return $this->model->where('default', '=', false)->where('enabled', '=', true)->first();
    }

    /**
     * Sets the passed model as the new default.
     *
     * @param  \Illuminate\Database\Eloquent\Model  &$model
     * @return void
     */
    protected function setNewDefault(&$model)
    {
        if ($current = $this->getDefaultRecord()) {
            $current->default = false;
            $current->save();
        }
        $model->default = true;
    }

    /**
     * Determines whether a record exists by a given code.
     *
     * @param  string  $code
     * @return bool
     */
    public function existsByCode($code)
    {
        return $this->model->where('code', '=', $code)->exists();
    }

    /**
     * Checks whether a record exists with the given hashed id.
     *
     * @param  string  $hashedId
     * @return bool
     */
    public function existsByHashedId($hashedId)
    {
        if (is_array($hashedId)) {
            $ids = $this->getDecodedIds($hashedId);

            return $this->model->whereIn('id', $ids)->count();
        }
        $id = $this->model->decodeId($hashedId);

        return $this->model->where($this->model->getTable().'.id', '=', $id)->exists();
    }

    public function getDataList()
    {
        return $this->model->get();
    }

    /**
     * Gets the attributes related to the model.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAttributes($id)
    {
        return $this->model->attributes()->get();
    }

    /**
     * Updates the attributes for a model.
     *
     * @param  string  $model
     * @param  array  $data
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updateAttributes($id, array $data)
    {
        $ids = [];
        $model = $this->getByHashedId($id);
        $updatedData = $model->attribute_data;
        foreach ($data['attributes'] as $attribute) {
            $ids[] = GetCandy::attributes()->getDecodedId($attribute);
        }
        $model->attributes()->sync($ids);

        return $model;
    }

    /**
     * Validates the integrity of the attribute data.
     *
     * @param  array  $data
     * @return bool
     */
    public function validateAttributeData(array $data)
    {
        foreach ($data as $locale => $value) {
            if (is_array($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks the structure of an array against another.
     *
     * @param  array|null  $structure
     * @param  array|null  $data
     * @return bool
     */
    protected function validateStructure(array $structure = null, $data = null)
    {
        foreach ($structure as $key => $value) {
            if (is_array($value)) {
                if (! is_array($data) || ! array_key_exists($key, $data)) {
                    return false;
                }

                return $this->validateStructure($structure[$key], $data[$key]);
            } else {
                return isset($data[$key]);
            }
        }

        return true;
    }

    public function getEnabled($value, $column = 'handle')
    {
        $query = $this->model->where('enabled', '=', true);
        if (is_array($value)) {
            return $query->whereIn($column, $value)->first();
        }

        return $query->where($column, '=', $value)->first();
    }

    public function getUniqueUrl($urls, $path = null)
    {
        $unique = [];

        if (is_array($urls)) {
            $previousUrl = null;
            foreach ($urls as $locale => $url) {
                $i = 1;
                while (SearchForRoute::run(['slug' => $url, 'path' => $path]) || $previousUrl == $url) {
                    $url = $url.'-'.$i;
                    $i++;
                }
                $unique[] = [
                    'locale' => $locale,
                    'path' => $path,
                    'slug' => $url,
                    'default' => $locale == app()->getLocale() ? true : false,
                ];
                $previousUrl = $url;
            }
        } else {
            $i = 1;
            $url = $urls;
            while (SearchForRoute::run(['slug' => $url])) {
                $url = $url.'-'.$i;
                $i++;
            }
            $unique[] = [
                'locale' => app()->getLocale(),
                'slug' => $url,
                'default' => true,
            ];
        }

        return $unique;
    }

    public function getSearchedIds($ids = [])
    {
        $parsedIds = [];
        foreach ($ids as $hash) {
            $parsedIds[] = $this->model->decodeId($hash);
        }

        $placeholders = implode(',', array_fill(0, count($parsedIds), '?')); // string for the query

        $query = $this->model->with([
            'routes',
        ])
            ->withoutGlobalScopes()
            ->whereIn('id', $parsedIds);

        if (count($parsedIds)) {
            $query = $query->orderByRaw("field(id,{$placeholders})", $parsedIds);
        }

        return $query->get();
    }

    /**
     * Gets the mapping for the channel data.
     *
     * @param  array  $data
     * @return array
     */
    protected function getChannelMapping($data)
    {
        $channelData = [];
        foreach ($data as $channel) {
            $channelModel = FetchChannel::run([
                'encoded_id' => $channel['id'],
            ]);
            $channelData[$channelModel->id] = [
                'published_at' => $channel['published_at'] ? Carbon::parse($channel['published_at']) : null,
            ];
        }

        return $channelData;
    }

    /**
     * Maps customer group data for a model.
     *
     * @param  array  $groups
     * @return array
     */
    protected function mapCustomerGroupData($groups)
    {
        $groupData = [];
        foreach ($groups as $group) {
            $groupModel = GetCandy::customerGroups()->getByHashedId($group['id']);
            $groupData[$groupModel->id] = [
                'visible' => $group['visible'],
                'purchasable' => $group['purchasable'],
            ];
        }

        return $groupData;
    }

    /**
     * Update a given resource from data.
     *
     * @param  string  $hashedId
     * @param  array  $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update($hashedId, array $data)
    {
        $model = $this->getByHashedId($hashedId, true);
        $model->attribute_data = $data['attribute_data'];

        if (! empty($data['customer_groups'])) {
            $groupData = $this->mapCustomerGroupData($data['customer_groups']['data']);
            $model->customerGroups()->sync($groupData);
        }

        if (! empty($data['channels']['data'])) {
            $model->channels()->sync(
                $this->getChannelMapping($data['channels']['data'])
            );
        }

        $model->save();

        event(new AttributableSavedEvent($model));

        return $model;
    }

    /**
     * Creates a URL for a product.
     *
     * @param  string  $hashedId
     * @param  array  $data
     * @return \GetCandy\Api\Core\Routes\Models\Route
     */
    public function createUrl($hashedId, array $data)
    {
        $model = $this->getByHashedId($hashedId, true);

        try {
            return SearchForRoute::run(['slug' => $data['slug']]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        }

        $route = $model->routes()->create([
            'locale' => $data['locale'],
            'slug' => $data['slug'],
            'description' => ! empty($data['description']) ? $data['description'] : null,
            'redirect' => ! empty($data['redirect']) ? true : false,
            'default' => false,
        ]);

        return $route;
    }

    /**
     * Sets the channel mapping.
     *
     * @param  array  $channels
     * @return array
     */
    protected function setChannelMapping($channels = [])
    {
        $channelData = [];
        foreach ($channels as $channel) {
            $channelModel = FetchChannel::run([
                'encoded_id' => $channel['id'],
            ]);
            $channelData[$channelModel->id] = [
                'published_at' => $channel['published_at'] ? Carbon::parse($channel['published_at']) : null,
            ];
        }

        return $channelData;
    }
}
