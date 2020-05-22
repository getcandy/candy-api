<?php

namespace GetCandy\Api\Core\Routes\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use NeonDigital\Drafting\Draftable;

class Route extends BaseModel
{
    use SoftDeletes,
        Draftable;

    protected $hashids = 'main';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'slug', 'default', 'redirect', 'description', 'locale', 'path',
    ];

    /**
     * Get all of the owning element models.
     */
    public function element()
    {
        return $this->morphTo();
    }
}
