<?php

namespace GetCandy\Api\Http\Controllers;

use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

class BaseController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ReturnsJsonResponses;

    /**
     * Parses included fields into an array.
     *
     * @param  mixed  $request
     * @return string[]
     */
    protected function parseIncludedFields($request)
    {
        if (! $request->fields) {
            return [];
        }

        return explode(',', $request->fields);
    }

    protected function parseIncludes($includes = null)
    {
        $includes = $includes ?: [];

        if ($includes && is_string($includes)) {
            $includes = explode(',', $includes);
        }

        return array_map(function ($inc) {
            return lcfirst(implode(array_map(function ($str) {
                return ucfirst($str);
            }, explode('_', $inc))));
        }, $includes);
    }
}
