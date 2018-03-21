<?php

namespace GetCandy\Api\Auth\Models;

use GetCandy\Api\Traits\Hashids;
use Laravel\Passport\HasApiTokens;
use GetCandy\Api\Orders\Models\Order;
use Spatie\Permission\Traits\HasRoles;
use GetCandy\Api\Baskets\Models\Basket;
use Illuminate\Notifications\Notifiable;
use GetCandy\Api\Addresses\Models\Address;
use GetCandy\Api\Traits\HasCustomerGroups;
use GetCandy\Api\Languages\Models\Language;
use GetCandy\Api\Customers\Models\CustomerGroup;
use Illuminate\Foundation\Auth\User as Authenticatable;
use GetCandy\Plugins\LegacyPassword\Models\LegacyPassword;

class User extends Authenticatable
{
    use Notifiable,
        Hashids,
        HasApiTokens,
        HasRoles;

    protected $hashids = 'user';

    public function getAuthPassword()
    {
        if (!$this->password) {
            $password = $this->legacypassword;
            return [
                'password' => $password->password,
                'salt' => $password->salt
            ];
        }
        return $this->password;
    }

    public function legacypassword()
    {
        return $this->hasOne(LegacyPassword::class);
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'firstname', 'lastname', 'email',  'password', 'role', 'company_name'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function groups()
    {
        return $this->belongsToMany(CustomerGroup::class);
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
        return $this->hasOne(Basket::class)->orderBy('created_at', 'DESC');
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
        return $this->hasMany(Order::class)->withoutGlobalScope('open')->withoutGlobalScope('not_expired');
    }
}
