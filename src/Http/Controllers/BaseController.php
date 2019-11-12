<?php

namespace GetCandy\Api\Http\Controllers;

use GetCandy\Api\Core\Traits\Fractal;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

class BaseController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, Fractal;

    /**
     * Parses included fields into an array.
     *
     * @param string $request
     * @return void
     */
    protected function parseIncludedFields($request)
    {
        if (! $request->fields) {
            return [];
        }

        return explode(',', $request->fields);
    }
}
