<?php

namespace GetCandy\Api\Http\Controllers\Discounts;

use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Discounts\UpdateRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GetCandy\Api\Http\Transformers\Fractal\Discounts\DiscountTransformer;

class DiscountController extends BaseController
{
    public function index(Request $request)
    {
        $paginator = app('api')->discounts()->getPaginatedData(
            $request->per_page,
            $request->current_page
        );

        return $this->respondWithCollection($paginator, new DiscountTransformer);
    }

    public function store(Request $request)
    {
        app('api')->discounts()->create($request->all());
    }

    public function update($id, UpdateRequest $request)
    {
        $discount = app('api')->discounts()->update($id, $request->all());

        return $this->respondWithItem($discount, new DiscountTransformer);
    }

    /**
     * Shows the discount resource.
     *
     * @param string $id
     *
     * @return void
     */
    public function show($id)
    {
        try {
            $discount = app('api')->discounts()->getByHashedId($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($discount, new DiscountTransformer);
    }
}
