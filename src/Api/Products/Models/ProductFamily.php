<?php

namespace GetCandy\Api\Products\Models;

use GetCandy\Api\Scaffold\BaseModel;
use GetCandy\Api\Traits\HasTranslations;
use GetCandy\Api\Traits\HasAttributes;

class ProductFamily extends BaseModel
{
    use HasAttributes;

    /**
     * The Hashid Channel for encoding the id
     * @var string
     */
    protected $hashids = 'product_family';

    protected $fillable = ['attribute_data'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
