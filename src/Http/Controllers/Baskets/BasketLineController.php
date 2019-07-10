<?php

namespace GetCandy\Api\Http\Controllers\Baskets;

use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Resources\Baskets\BasketResource;
use GetCandy\Api\Http\Requests\Baskets\CreateLineRequest;
use GetCandy\Api\Core\Baskets\Factories\BasketLineFactory;

class BasketLineController extends BaseController
{
    /**
     * @var BasketLineFactory
     */
    protected $factory;

    public function __construct(BasketLineFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Store one or more new basket lines, and associate them with a basket ID.
     *
     * @param string $id
     * @param CreateLineRequest $request
     *
     * @return BasketResource|array
     */
    public function store(string $id, CreateLineRequest $request)
    {
        $request['basket_id'] = $id;

        try {
            $basket = app('api')->basketLines()->addLines($request->all(), $request->user());
        } catch (\Illuminate\Database\QueryException $e) {
            return $this->errorUnprocessable(trans('getcandy::validation.max_qty'));
        }

        return new BasketResource($basket);
    }
}
