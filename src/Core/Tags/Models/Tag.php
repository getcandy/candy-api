<?php

namespace GetCandy\Api\Core\Tags\Models;

use GetCandy;
use GetCandy\Api\Core\Scaffold\BaseModel;

class Tag extends BaseModel
{
    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
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
        $this->attributes['name'] = GetCandy::tags()->getFormattedTagName($value);
    }

    /**
     * Get all of the tags for the post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
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
