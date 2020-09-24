<?php

namespace GetCandy\Api\Http\Validators;

use DB;

class DatabaseValidator
{
    public function uniqueWith($attribute, $value, $parameters, $validator)
    {
        $query = DB::table($parameters[0])->where($attribute, '=', $value);

        $routeId = $parameters[3] ?? null;

        if ($routeId) {
            $query = $query->where('id', '!=', $routeId);
        }

        if (empty($parameters[2])) {
            return ! $query->whereNull($parameters[1])->exists();
        }

        return ! $query->where($parameters[1], '=', $parameters[2])->exists();
    }
}
