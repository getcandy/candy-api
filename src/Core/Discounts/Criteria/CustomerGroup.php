<?php

namespace GetCandy\Api\Core\Discounts\Criteria;

use GetCandy;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Discounts\Contracts\DiscountCriteriaContract;
use GetCandy\Api\Core\Foundation\Actions\DecodeIds;

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
        return collect(
            DecodeIds::run([
                'model' => CustomerGroup::class,
                'encoded_ids' => $this->criteria['value'],
            ])
        );
    }

    public function check($user)
    {
        // dd($this->getRealIds());
        // return GetCandy::customerGroups()->userIsInGroup($this->value, $user);
        return false;
    }
}
