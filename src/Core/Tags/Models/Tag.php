<?php

namespace GetCandy\Api\Core\Tags\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class Tag extends BaseModel
{
    protected $hashids = 'tag';

    /**
     * The tags that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = app('api')->tags()->getFormattedTagName($value);
    }

    /**
     * Get all of the tags for the post.
     */
    public function taggables()
    {
        return $this->hasMany(Taggable::class);
    }

    public function assets()
    {
        return $this->morphedByMany(Asset::class, 'taggable');
    }
}
