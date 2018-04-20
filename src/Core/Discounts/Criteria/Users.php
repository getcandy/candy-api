<?php

namespace GetCandy\Api\Core\Discounts\Criteria;

use GetCandy\Api\Core\Discounts\Contracts\DiscountCriteriaContract;

class Users implements DiscountCriteriaContract
{
    protected $value;

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function check($user)
    {
        if (! $user) {
            return false;
        }

        return $this->value->contains($user->id);
    }
}
