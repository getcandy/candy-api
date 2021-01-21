<?php

namespace GetCandy\Api\Core\Attributes\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Traits\HasTranslations;

class Attribute extends BaseModel
{
    use HasTranslations;

    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
    protected $hashids = 'attribute';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'handle',
        'position',
        'variant',
        'lookups',
        'required',
        'searchable',
        'filterable',
        'type',
    ];

    public function group()
    {
        return $this->belongsTo(AttributeGroup::class, 'group_id');
    }

    /**
     * Get all of the tags for the post.
     */
    public function attributables()
    {
        return $this->hasMany(Attributable::class);
    }

    /**
     * Sets the name attribute to a json string.
     *
     * @param  array  $value
     * @return void
     */
    public function setLookupsAttribute($value)
    {
        if ($value) {
            if (is_array($value)) {
                $this->attributes['lookups'] = json_encode($value);
            } else {
                $this->attributes['lookups'] = $value;
            }
        }
    }

    public function scopeHandle($query, $handle)
    {
        return $query->where('handle', 'LIKE', '%'.$handle.'%');
    }

    public function getLookupsAttribute($value)
    {
        return json_decode($value, true);
    }

    public function scopeFilterable($query)
    {
        return $query->whereFilterable(true);
    }
}
