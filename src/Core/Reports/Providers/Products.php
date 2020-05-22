<?php

namespace GetCandy\Api\Core\Reports\Providers;

use DB;

class Products extends AbstractProvider
{
    public function attribute($attribute, $value, $expression = 'EQUALS')
    {
        return DB::table('products')
        ->select(
            'products.id',
            'sku',
            DB::RAW($this->getJsonColumn($attribute).'as value'),
            DB::RAW($this->getJsonColumn('name').'as name')
        )->where(
            DB::RAW($this->getJsonColumn($attribute)),
            $this->getExpression($expression),
            $value
        )->whereNull('deleted_at')
            ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
            ->groupBy('products.id')
            ->paginate(60);
    }

    public function get()
    {
    }
}
