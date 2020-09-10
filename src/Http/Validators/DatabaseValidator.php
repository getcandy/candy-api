<?php

namespace GetCandy\Api\Http\Validators;

use DB;

class DatabaseValidator
{
    public function uniqueWith($attribute, $value, $parameters, $validator)
    {
        $query = DB::table($parameters[0])->where($attribute, '=', $value);

        if (empty($parameters[2])) {
            $query = $query->whereNull($parameters[1]);
        } else {
            $query = $query->where($parameters[1], '=', $parameters[2]);
        }

        return ! $query->exists();
    }
}
