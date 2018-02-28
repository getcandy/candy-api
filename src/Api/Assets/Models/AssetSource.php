<?php

namespace GetCandy\Api\Assets\Models;

use GetCandy\Api\Scaffold\BaseModel;

class AssetSource extends BaseModel
{
    protected $hashids = 'assets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'handle',
        'disk',
        'default'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}
