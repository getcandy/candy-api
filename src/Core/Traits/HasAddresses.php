<?php

namespace GetCandy\Api\Core\Traits;

use GetCandy\Api\Core\Addresses\Models\Address;

trait HasAddresses
{
    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable');
    }
}
