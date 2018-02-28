<?php

namespace GetCandy\Api\Routes\Models;

use GetCandy\Api\Channels\Models\Channel;
use GetCandy\Api\Languages\Models\Language;
use GetCandy\Api\Layouts\Models\Layout;
use GetCandy\Api\Scaffold\BaseModel;

class Route extends BaseModel
{
    protected $hashids = 'main';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'slug', 'default', 'redirect', 'description', 'locale'
    ];

    /**
     * Get all of the owning element models.
     */
    public function element()
    {
        return $this->morphTo();
    }
}
