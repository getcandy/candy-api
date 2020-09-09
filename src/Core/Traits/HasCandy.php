<?php

namespace GetCandy\Api\Core\Traits;

use GetCandy\Api\Core\Addresses\Models\Address;
use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Baskets\Models\SavedBasket;
use GetCandy\Api\Core\Customers\Models\Customer;
use GetCandy\Api\Core\Languages\Models\Language;
use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Payments\Models\ReusablePayment;
use Spatie\Permission\Traits\HasRoles;

trait HasCandy
{
    use Hashids,
        HasRoles;

    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
    protected $hashids = 'user';

    public function inGroup($group)
    {
        return $this->groups()->where('handle', '=', $group)->exists();
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function getFieldsAttribute($value)
    {
        return json_decode($value, true);
    }

    public function baskets()
    {
        return $this->hasMany(Basket::class);
    }

    public function latestBasket()
    {
        return $this->hasOne(Basket::class)
            ->doesntHave('savedBasket')
            ->orderBy('created_at', 'DESC');
    }

    public function savedBaskets()
    {
        return $this->hasManyThrough(SavedBasket::class, Basket::class);
    }

    public function reusablePayments()
    {
        return $this->hasMany(ReusablePayment::class);
    }

    public function setFieldsAttribute($value)
    {
        $this->attributes['fields'] = json_encode($value);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class)->orderBy('reference', 'desc')->withoutGlobalScope('open')->withoutGlobalScope('not_expired');
    }

    public function firstOrder()
    {
        return $this->hasOne(Order::class)
            ->withoutGlobalScopes()
            ->whereNotNull('placed_at')
            ->orderBy('placed_at', 'asc');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
