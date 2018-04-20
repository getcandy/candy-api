<?php

namespace GetCandy\Api\Core\Traits;

use GetCandy\Api\Core\Scopes\CustomerGroupScope;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;

trait HasCustomerGroups
{
    public static function boot()
    {
        parent::boot();
        static::addGlobalScope(new CustomerGroupScope);
    }

    public function customerGroups()
    {
        return $this->belongsToMany(CustomerGroup::class)->withPivot(['visible', 'purchasable']);
    }
}
