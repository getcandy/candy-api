<?php

namespace GetCandy\Api\Core\Products\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Traits\HasAttributes;

class ProductFamily extends BaseModel
{
    use HasAttributes;
    /**
     * The Hashid Channel for encoding the id.
     * @var string
     */
    protected $hashids = 'product_family';

    protected $fillable = ['attribute_data'];

    public function getNameAttribute($value)
    {
        return json_decode($value, true);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Scope a query to only include the default record.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefault($query)
    {
        return $query;
    }
}
