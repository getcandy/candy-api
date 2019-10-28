<?php

namespace GetCandy\Api\Core\Reports\Providers;

use DB;

class Attributes extends AbstractProvider
{
    public function attribute($attribute)
    {
        return DB::table('products')->select(
            DB::raw($this->getJsonColumn($attribute) . 'as value'),
            DB::RAW('COUNT(*) as count')
        )->groupBy(DB::raw($this->getJsonColumn($attribute)))->paginate(25);
    }

    public function get()
    {
    }
}
