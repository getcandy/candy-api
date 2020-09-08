<?php

namespace GetCandy\Api\Http\Controllers\Customers;

use GetCandy;
use GetCandy\Api\Core\Customers\Resources\CustomerGroupCollection;
use GetCandy\Api\Http\Controllers\BaseController;
use Illuminate\Http\Request;

class CustomerGroupController extends BaseController
{
    public function index(Request $request)
    {
        $groups = GetCandy::customerGroups()->getPaginatedData($request->per_page);

        return new CustomerGroupCollection($groups);
    }
}
