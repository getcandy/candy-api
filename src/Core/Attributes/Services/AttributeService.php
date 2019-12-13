<?php

namespace GetCandy\Api\Core\Attributes\Services;

use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Exceptions\DuplicateValueException;
use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Core\Attributes\Events\AttributeSavedEvent;

class AttributeService extends BaseService
{
    /**
     * @var AttributeGroup
     */
    protected $model;

    public function __construct()
    {
        $this->model = new Attribute();
    }

    /**
     * Creates a resource from the given data.
     *
     * @param  array  $data
     *
     * @return GetCandy\Api\Core\Models\Attribute
     */
    public function create(array $data)
    {
        $attributeGroup = app('api')->attributeGroups()->getByHashedId($data['group_id']);

        if (! $attributeGroup) {
            abort(400, 'Attribute group with ID "'.$data['group_id'].'" doesn\'t exist');
        }

        $result = $attributeGroup->attributes()->create([
            'name' => $data['name'],
            'handle' => $data['handle'],
            'type' => $data['type'] ?? 'text',
            'position' => $this->getNewPositionForGroup($attributeGroup->id),
            'variant' => ! empty($data['variant']) ? $data['variant'] : false,
            'searchable' => ! empty($data['searchable']) ? $data['searchable'] : false,
            'filterable' => ! empty($data['filterable']) ? $data['filterable'] : false,
        ]);

        // event(new AttributeSavedEvent);

        return $result;
    }

    protected function getNewPositionForGroup($groupId)
    {
        $attribute = $this->getLastItem($groupId);

        return $attribute ? $attribute->position + 1 : 1;
    }

    public function getAttributables(array $hashedIds, $type = null)
    {
        $ids = [];
        foreach ($hashedIds as $hash) {
            $ids[] = $this->model->decodeId($hash);
        }
        $query = $this->model->with(['attributables', 'attributables.records']);
        if ($type) {
            $query = $this->model->with(['attributables' => function ($query) use ($type) {
                $query->where('attributable_type', '=', $type);
            }, 'attributables.records']);
        }

        return $query->find($ids);
    }

    public function getHandles()
    {
        return $this->model->select(['handle', 'id'])->get()->toArray();
    }

    /**
     * Updates the positions of attributes.
     * @param  array  $data
     *
     * @throws Symfony\Component\HttpKernel\Exception\HttpException
     * @throws GetCandy\Api\Core\Exceptions\DuplicateValueException
     *
     * @return bool
     */
    public function updateAttributePositions(array $data)
    {

        // Test for duplicates without hitting the database
        if (count($data['attributes']) > count(array_unique($data['attributes']))) {
            throw new DuplicateValueException(trans('validation.attributes.groups.dupe_position'), 1);
        }

        $parsedAttributes = [];

        foreach ($data['attributes'] as $attributeId => $position) {
            $decodedId = (new Attribute)->decodeId($attributeId);
            if (! $decodedId) {
                abort(422, trans('validation.attributes.groups.invalid_id', ['id' => $attributeId]));
            }
            $parsedAttributes[$decodedId] = $position;
        }

        $attributes = $this->getByHashedIds(array_keys($data['attributes']));

        foreach ($attributes as $attribute) {
            $attribute->position = $parsedAttributes[$attribute->id];
            $attribute->save();
        }

        return true;
    }

    /**
     * Updates a resource from the given data.
     *
     * @param  string $id
     * @param  array  $data
     *
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return GetCandy\Api\Core\Models\Attribute
     */
    public function update($hashedId, array $data)
    {
        $attribute = $this->getByHashedId($hashedId);

        if (! $attribute) {
            abort(404);
        }

        $attribute->fill($data);
        $attribute->save();

        event(new AttributeSavedEvent);

        return $attribute;
    }

    /**
     * Deletes a resource by its given hashed ID.
     *
     * @param  string $id
     *
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return bool
     */
    public function delete($id)
    {
        $attribute = $this->getByHashedId($id);

        if (! $attribute) {
            abort(404);
        }

        return $attribute->delete();
    }

    /**
     * Returns attributes for a group.
     * @param  string $groupId
     * @return Collection
     */
    public function getAttributesForGroup($groupId)
    {
        return $this->model->where('group_id', '=', $groupId)->get();
    }

    /**
     * Gets the last attribute for a groupo.
     * @param  string $groupId
     * @return null|Attribute
     */
    public function getLastItem($groupId)
    {
        return $this->model->orderBy('position', 'desc')->where('group_id', '=', $groupId)->first();
    }

    /**
     * Checks whether a attribute name exists in a group.
     * @param  string $value
     * @param  string $groupId
     * @param  string $attributeId
     * @return bool
     */
    public function nameExistsInGroup($value, $groupId, $attributeId = null)
    {
        $result = $this->model->where('name', '=', $value)
                        ->where('group_id', '=', $groupId);

        if ($attributeId) {
            $result->where('id', '!=', $attributeId);
        }

        return ! $result->exists();
    }

    public function getByHandles(array $handles)
    {
        return $this->model->whereIn('handle', $handles)->get();
    }

    public function getFilterable()
    {
        return $this->model->where('filterable', true)->get();
    }

    public function getSearchable()
    {
        return $this->model->where('searchable', true)->get();
    }
}
