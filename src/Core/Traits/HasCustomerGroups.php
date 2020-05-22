<?php

namespace GetCandy\Api\Core\Traits;

use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Scopes\CustomerGroupScope;

trait HasCustomerGroups
{
    public static function bootHasCustomerGroups()
    {
        static::addGlobalScope(new CustomerGroupScope);
    }

    public function customerGroups()
    {
        return $this->belongsToMany(CustomerGroup::class)->withPivot(['visible', 'purchasable']);
    }
}
