<?php

namespace GetCandy\Api\Attributes\Services;

use GetCandy\Api\Attributes\Models\Attribute;
use GetCandy\Api\Scaffold\BaseService;
use GetCandy\Exceptions\DuplicateValueException;

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
     * Creates a resource from the given data
     *
     * @param  array  $data
     *
     * @return GetCandy\Api\Models\Attribute
     */
    public function create(array $data)
    {
        $attributeGroup = app('api')->attributeGroups()->getByHashedId($data['group_id']);

        if (!$attributeGroup) {
            abort(400, 'Attribute group with ID "' . $data['group_id'] . '" doesn\'t exist');
        }

        $result = $attributeGroup->attributes()->create([
            'name' => $data['name'],
            'handle' => $data['handle'],
            'position' => $this->getNewPositionForGroup($attributeGroup->id),
            'variant' => !empty($data['variant']) ? $data['variant'] : false,
            'searchable' => !empty($data['searchable']) ? $data['searchable'] : false,
            'filterable' => !empty($data['filterable']) ? $data['filterable'] : false
        ]);

        return $result;
    }

    protected function getNewPositionForGroup($groupId)
    {
        $attribute = $this->getLastItem($groupId);
        return $attribute->position + 1;
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
     * Updates the positions of attributes
     * @param  array  $data
     *
     * @throws Symfony\Component\HttpKernel\Exception\HttpException
     * @throws GetCandy\Api\Exceptions\DuplicateValueException
     *
     * @return Boolean
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
            if (!$decodedId) {
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
     * Updates a resource from the given data
     *
     * @param  string $id
     * @param  array  $data
     *
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return GetCandy\Api\Models\Attribute
     */
    public function update($hashedId, array $data)
    {
        $attribute = $this->getByHashedId($hashedId);

        if (!$attribute) {
            abort(404);
        }

        $attribute->fill($data);
        $attribute->save();

        return $attribute;
    }

    /**
     * Deletes a resource by its given hashed ID
     *
     * @param  string $id
     *
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Boolean
     */
    public function delete($id)
    {
        $attribute = $this->getByHashedId($id);


        if (!$attribute) {
            abort(404);
        }

        return $attribute->delete();
    }

    /**
     * Returns attributes for a group
     * @param  String $groupId
     * @return Collection
     */
    public function getAttributesForGroup($groupId)
    {
        return $this->model->where('group_id', '=', $groupId)->get();
    }

    /**
     * Gets the last attribute for a groupo
     * @param  String $groupId
     * @return null|Attribute
     */
    public function getLastItem($groupId)
    {
        return $this->model->orderBy('position', 'desc')->where('group_id', '=', $groupId)->first();
    }

    /**
     * Checks whether a attribute name exists in a group
     * @param  String $value
     * @param  String $groupId
     * @param  String $attributeId
     * @return Boolean
     */
    public function nameExistsInGroup($value, $groupId, $attributeId = null)
    {
        $result = $this->model->where('name', '=', $value)
                        ->where('group_id', '=', $groupId);

        if ($attributeId) {
            $result->where('id', '!=', $attributeId);
        }

        return !$result->exists();
    }


    public function getByHandles(array $handles)
    {
        return $this->model->whereIn('handle', $handles)->get();
    }
}
