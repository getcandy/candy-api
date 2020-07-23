<?php

namespace GetCandy\Api\Http\Controllers\Customers;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Customers\CreateRequest;
use GetCandy\Api\Http\Resources\Users\UserCollection;
use GetCandy\Api\Http\Resources\Users\UserResource;
use Illuminate\Http\Request;

class CustomerController extends BaseController
{
    /**
     * Shows all the customers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \GetCandy\Api\Http\Resources\Users\UserCollection
     */
    public function index(Request $request)
    {
        $customers = GetCandy::customers()->getPaginatedData(
            $request->length,
            $request->page,
            $request->keywords,
            explode(',', $request->includes)
        );

        return new UserCollection($customers);
    }

    public function show($id, Request $request)
    {
        $customer = GetCandy::customers()->getByHashedId($id, explode(',', $request->includes));

        return new UserResource($customer);
    }

    /**
     * Handles request to store a customer.
     *
     * @param  \GetCandy\Api\Http\Requests\Customers\CreateRequest  $request
     * @return array
     */
    public function store(CreateRequest $request)
    {
        return new UserResource(
            GetCandy::customers()->register($request->all())
        );
    }
}
