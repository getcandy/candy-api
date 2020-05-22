<?php

namespace GetCandy\Api\Http\Controllers\Customers;

use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Resources\Users\UserResource;
use GetCandy\Api\Http\Resources\Users\UserCollection;
use GetCandy\Api\Http\Requests\Customers\CreateRequest;
use GetCandy\Api\Http\Transformers\Fractal\Users\UserTransformer;

class CustomerController extends BaseController
{
    /**
     * Shows all the customers.
     *
     * @return array
     */
    public function index(Request $request)
    {
        $customers = app('api')->customers()->getPaginatedData(
            $request->length,
            $request->page,
            $request->keywords,
            explode(',', $request->includes)
        );

        return new UserCollection($customers);
    }

    public function show($id, Request $request)
    {
        $customer = app('api')->customers()->getByHashedId($id, explode(',', $request->includes));

        return new UserResource($customer);
    }

    /**
     * Handles request to store a customer.
     *
     * @param CreateRequest $request
     *
     * @return array
     */
    public function store(CreateRequest $request)
    {
        $customer = app('api')->customers()->register($request->all());

        return $this->respondWithItem($customer, new UserTransformer);
    }
}
