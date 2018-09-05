<?php

namespace GetCandy\Api\Core\Products\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class ProductRecommendation extends BaseModel
{
    protected $fillable = ['product_id'];

    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(Product::class, 'related_product_id');
    }
}
