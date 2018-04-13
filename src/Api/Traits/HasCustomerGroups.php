<?php

namespace GetCandy\Api\Traits;

use GetCandy\Api\Customers\Models\CustomerGroup;
use GetCandy\Api\Scopes\CustomerGroupScope;

trait HasCustomerGroups
{
    public static function boot()
    {
        parent::boot();
        static::addGlobalScope(new CustomerGroupScope());
    }

    public function customerGroups()
    {
        return $this->belongsToMany(CustomerGroup::class)->withPivot(['visible', 'purchasable']);
    }
}
