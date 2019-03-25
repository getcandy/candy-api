<?php

namespace GetCandy\Api\Core\Baskets\Validators;

use DB;
use GetCandy\Api\Core\Products\Models\ProductVariant;

class BasketValidator
{
    public function uniqueLines($value, $parameters, $basketId, $validator)
    {
        $unique = collect($parameters)->unique('id');

        return $unique->count() == count($parameters);
    }

    public function inStock($value, $variant, $basketId, $validator)
    {
        return app('api')->productVariants()->canAddToBasket($variant['id'], $variant['quantity'] ?? null);
    }

    public function minQuantity($value, $parameters, $basketId, $validator)
    {
        $variant = array_get($validator->getData(), str_replace('.quantity', '', $value));

        $realId = (new ProductVariant)->decodeId($variant['id']);

        $row = DB::table('product_variants')->select('min_qty')->find($realId);

        $result = $row->min_qty <= $variant['quantity'];

        return $result;
    }
}
