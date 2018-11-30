<?php

namespace GetCandy\Api\Core\Customers\Services;

use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;

class CustomerGroupService extends BaseService
{
    public function __construct()
    {
        $this->model = new CustomerGroup;
    }

    public function getGroupsWithAvailability($model, $relation)
    {
        $groups = $this->model->with([camel_case($relation) => function ($q) use ($model, $relation) {
            $q->where($relation.'.id', $model->id);
        }])->get();
        foreach ($groups as $group) {
            $model = $group->{camel_case($relation)}->first();
            $group->published_at = $model ? $model->pivot->published_at : null;
            $group->visible = $model ? $model->pivot->visible : false;
            $group->purchasable = $model ? $model->pivot->purchasable : false;
        }

        return $groups;
    }

    public function create(array $data)
    {
        $group = $this->model;
        $group->name = $data['name'];
        $group->handle = $data['handle'];
        $group->default = ! empty($data['default']) ? $data['default'] : false;
        $group->system = ! empty($data['system']) ? $data['system'] : false;

        $group->save();

        return $group;
    }

    public function getGuestId()
    {
        return $this->getGuest()->id;
    }

    public function getGuest()
    {
        return $this->model->where('default', '=', true)->first();
    }

    public function userIsInGroup($group, $user)
    {
        return $user->groups()->where('handle', '=', $group)->exists();
    }
}
