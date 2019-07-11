<?php

namespace GetCandy\Api\Http\Controllers\Baskets;

use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Resources\Baskets\BasketResource;
use GetCandy\Api\Http\Requests\Baskets\CreateLineRequest;
use GetCandy\Api\Http\Requests\Baskets\UpdateLineRequest;
use GetCandy\Api\Core\Baskets\Factories\BasketLineFactory;
use GetCandy\Api\Http\Requests\Baskets\DeleteLinesRequest;
use GetCandy\Api\Http\Requests\Baskets\ChangeQuantityRequest;

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
     * @param CreateLineRequest $request
     * @return array|BasketResource
     */
    public function storeCurrent(CreateLineRequest $request)
    {
        return $this->store(null, $request);
    }

    /**
     * Store one or more new basket lines, and associate them with a basket ID.
     *
     * @param string $id
     * @param CreateLineRequest $request
     *
     * @return array|BasketResource
     */
    public function store(string $id, CreateLineRequest $request)
    {
        if ($request['basket_id'] !== 'current') {
            $request['basket_id'] = $id;
        }

        try {
            $basket = app('api')->baskets()->addLines($request->all(), $request->user());
        } catch (\Illuminate\Database\QueryException $e) {
            return $this->errorUnprocessable(trans('getcandy::validation.max_qty'));
        }

        return new BasketResource($basket);
    }

    /**
     * Update a basket line's quantity.
     *
     * @param string $id
     * @param UpdateLineRequest $request
     *
     * @return BasketResource
     */
    public function update(string $id, UpdateLineRequest $request)
    {
        $quantity = $request['quantity'];

        $basket = app('api')->basketLines()->updateQuantity($id, $quantity);

        return new BasketResource($basket);
    }

    /**
     * Increase a basketLine's quantity.
     *
     * @param string $id
     * @param ChangeQuantityRequest $request
     *
     * @return BasketResource
     */
    public function addQuantity(string $id, ChangeQuantityRequest $request)
    {
        $quantity = $request['quantity'] ?? 1;

        $basket = app('api')->basketLines()->changeQuantity($id, $quantity);

        return new BasketResource($basket);
    }

    /**
     * Decrease a basketLine's quantity.
     *
     * @param string $id
     * @param ChangeQuantityRequest $request
     *
     * @return BasketResource
     */
    public function removeQuantity(string $id, ChangeQuantityRequest $request)
    {
        $quantity = ($request['quantity'] ?? 1) * -1;

        $basket = app('api')->basketLines()->changeQuantity($id, $quantity);

        return new BasketResource($basket);
    }

    /**
     * Handle the request to delete a basket.
     *
     * @param DeleteLinesRequest $request
     *
     * @return BasketResource
     */
    public function destroy(DeleteLinesRequest $request)
    {
        $basket = app('api')->basketLines()->destroy($request->lines);

        return new BasketResource($basket);
    }
}
