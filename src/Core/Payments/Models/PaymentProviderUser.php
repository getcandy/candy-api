<?php

namespace GetCandy\Api\Core\Payments\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class PaymentProviderUser extends BaseModel
{
    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
    protected $hashids = 'main';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function scopeProvider($query, $provider)
    {
        return $query->whereProvider($provider);
    }
}
