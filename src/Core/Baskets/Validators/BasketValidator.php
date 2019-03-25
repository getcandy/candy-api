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

        $validator->addReplacer('min_quantity', function($message, $attribute, $rule, $parameters) use ($row) {
            return str_replace([':min_qty'], [$row->min_qty], $message);
        });

        $result = $row->min_qty <= $variant['quantity'];

        return $result;
    }

    public function minBatch($value, $parameters, $basketId, $validator)
    {
        $variant = array_get($validator->getData(), str_replace('.quantity', '', $value));

        $realId = (new ProductVariant)->decodeId($variant['id']);

        $row = DB::table('product_variants')->select('min_batch')->find($realId);

        $validator->addReplacer('min_batch', function($message, $attribute, $rule, $parameters) use ($row) {
            return str_replace([':min_batch'], [$row->min_batch], $message);
        });

        return ($variant['quantity'] % $row->min_batch) === 0;
    }
}
