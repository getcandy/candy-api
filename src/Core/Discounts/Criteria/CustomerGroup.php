<?php

namespace GetCandy\Api\Core\Discounts\Criteria;

use GetCandy\Api\Core\Discounts\Contracts\DiscountCriteriaContract;

class CustomerGroup implements DiscountCriteriaContract
{
    protected $value;

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function setCriteria($criteria)
    {
        $this->criteria = json_decode($criteria, true);
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getLabel()
    {
        return 'Customer Group';
    }

    public function getHandle()
    {
        return 'customer_group';
    }

    protected function getRealIds()
    {
        return collect(app('api')->customerGroups()->getDecodedIds($this->criteria['value']));
    }

    public function check($user)
    {
        if (!$user && !$this->value) {
            return false;
        }

        $passes = false;

        foreach ($this->value as $group) {
            if (!$user && $group->default) {
                $passes = true;
                continue;
            }

            if (!$user) {
                continue;
            }
        }

        // If we have a user, check if they're in the group.
        if ($user) {
            $passes = $user->groups->filter(function ($group) {
                return in_array($group->id, $this->value->pluck('id')->toArray());
            })->count();
        }

        return $passes;
    }
}
