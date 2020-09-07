<?php

namespace GetCandy\Api\Core\Customers\Models;

use GetCandy;
use GetCandy\Api\Core\Scaffold\BaseModel;

class Customer extends BaseModel
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function getFieldsAttribute($val)
    {
        return json_decode($val);
    }

    public function customerGroups()
    {
        return $this->belongsToMany(CustomerGroup::class);
    }

    public function getFullNameAttribute()
    {
        return trim($this->title.' '.$this->firstname.' '.$this->lastname);
    }

    public function setFieldsAttribute($val)
    {
        $this->attributes['fields'] = json_encode($val);
    }

    public function users()
    {
        return $this->hasMany(GetCandy::getUserModel());
    }
}
