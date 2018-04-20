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
        // dd($this->getRealIds());
        // return app('api')->customerGroups()->userIsInGroup($this->value, $user);
        return false;
    }
}
