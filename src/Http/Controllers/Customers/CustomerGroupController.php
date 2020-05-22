<?php

namespace GetCandy\Api\Http\Controllers\Customers;

use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Resources\Customers\CustomerGroupCollection;

class CustomerGroupController extends BaseController
{
    public function index(Request $request)
    {
        $groups = app('api')->customerGroups()->getPaginatedData($request->per_page);

        return new CustomerGroupCollection($groups);
    }
}
