<?php

namespace GetCandy\Api\Core\Products\Validators;

class ProductValidator
{
    public function available($value, $variantId)
    {
        return app('api')->productVariants()->variantIsAvailable($variantId);
    }
}
