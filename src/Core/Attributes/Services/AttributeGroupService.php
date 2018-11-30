<?php

namespace GetCandy\Api\Core\Attributes\Services;

use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Exceptions\DuplicateValueException;
use GetCandy\Api\Core\Attributes\Models\AttributeGroup;

class AttributeGroupService extends BaseService
{
    /**
     * @var AttributeGroup
     */
    protected $model;

    public function __construct()
    {
        $this->model = new AttributeGroup();
    }

    /**
     * Creates a resource from the given data.
     *
     * @param  array  $data
     *
     * @return AttributeGroup
     */
    public function create(array $data)
    {
        $group = $this->model;
        $group->name = $data['name'];
        $group->handle = str_slug($data['handle']);
        $group->position = $this->model->count() + 1;
        $group->save();

        return $group;
    }

    /**
     * Updates a resource from the given data.
     *
     * @param  string $id
     * @param  array  $data
     *
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws GetCandy\Api\Core\Exceptions\MinimumRecordRequiredException
     *
     * @return GetCandy\Api\Core\Models\AttributeGroup
     */
    public function update($hashedId, array $data)
    {
        $group = $this->getByHashedId($hashedId);

        if (! $group) {
            return;
        }
        if (isset($data['handle'])) {
            $group->handle = $data['handle'];
        }

        $group->fill($data);
        $group->save();

        return $group;
    }

    /**
     * Updates the positions of attribute groups.
     * @param  array  $data
     *
     * @throws Symfony\Component\HttpKernel\Exception\HttpException
     * @throws GetCandy\Api\Core\Exceptions\DuplicateValueException
     *
     * @return bool
     */
    public function updateGroupPositions(array $data)
    {
        // Test for duplicates without hitting the database
        if (count($data['groups']) > count(array_unique($data['groups']))) {
            throw new DuplicateValueException(trans('validation.attributes.groups.dupe_position'), 1);
        }

        $parsedGroups = [];

        foreach ($data['groups'] as $groupId => $position) {
            $decodedId = $this->getDecodedId($groupId);
            if (! $decodedId) {
                abort(422, trans('validation.attributes.groups.invalid_id', ['id' => $groupId]));
            }
            $parsedGroups[$decodedId] = $position;
        }

        $groups = $this->getByHashedIds(array_keys($data['groups']));

        foreach ($groups as $group) {
            $group->position = $parsedGroups[$group->id];
            $group->save();
        }

        return true;
    }

    /**
     * Deletes a resource by its given hashed ID.
     *
     * @param  string $id
     *
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws Symfony\Component\HttpKernel\Exception\HttpException
     *
     * @return bool
     */
    public function delete($id, $adopterId = null, $deleteAttributes = false)
    {
        $group = $this->getByHashedId($id);

        if (! $group) {
            abort(404);
        }

        if ($adopterId) {
            $adopter = $this->getByHashedId($adopterId);
            if (! $adopter) {
                abort(422);
            }
            $adopter->attributes()->saveMany($group->attributes);
        }

        foreach ($group->attributes()->get() as $attribute) {
            $attribute->delete();
        }

        $group->delete();

        \DB::transaction(function () {
            $i = 1;
            foreach ($this->model->orderBy('position', 'asc')->get() as $group) {
                $group->position = $i;
                $i++;
                $group->save();
            }
        });

        return true;
    }
}
