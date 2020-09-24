<?php

namespace GetCandy\Api\Http\Controllers\Discounts;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Discounts\UpdateRequest;
use GetCandy\Api\Http\Resources\Discounts\DiscountCollection;
use GetCandy\Api\Http\Resources\Discounts\DiscountResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class DiscountController extends BaseController
{
    public function index(Request $request)
    {
        $paginator = GetCandy::discounts()->getPaginatedData(
            $request->per_page,
            $request->current_page,
            $request->includes ? explode(',', $request->includes) : null
        );

        return new DiscountCollection($paginator);
    }

    public function store(Request $request)
    {
        // TODO: Add validation
        return new DiscountResource(
            GetCandy::discounts()->create($request->all())
        );
    }

    public function update($id, UpdateRequest $request)
    {
        return new DiscountResource(
            GetCandy::discounts()->update($id, $request->all())
        );
    }

    /**
     * Shows the discount resource.
     *
     * @param  string  $id
     * @param  \Illuminate\Http\Request  $request
     * @return array|\GetCandy\Api\Http\Resources\Discounts\DiscountResource
     */
    public function show($id, Request $request)
    {
        try {
            $discount = GetCandy::discounts()->getByHashedId(
                $id,
                $request->includes ? explode(',', $request->includes) : null
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new DiscountResource($discount);
    }
}
