<?php

namespace GetCandy\Api\Core\Products\Models;

use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Core\Scaffold\BaseModel;

class ProductFamily extends BaseModel
{
    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
    protected $hashids = 'product_family';

    protected $guarded = [];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get all of the attributes for the product family.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function attributes()
    {
        return $this->morphToMany(Attribute::class, 'attributable')->orderBy('position', 'asc');
    }

    /**
     * Scope a query to only include the default record.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefault($query)
    {
        return $query;
    }
}
