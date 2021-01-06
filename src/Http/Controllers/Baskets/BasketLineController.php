<?php

namespace GetCandy\Api\Http\Controllers\Baskets;

use GetCandy;
use Illuminate\Http\Request;
use GetCandy\Api\Core\Baskets\Models\BasketLine;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Resources\Baskets\BasketResource;
use GetCandy\Api\Http\Requests\Baskets\UpdateLineRequest;
use GetCandy\Api\Core\Baskets\Factories\BasketLineFactory;
use GetCandy\Api\Http\Requests\Baskets\CreateLinesRequest;
use GetCandy\Api\Http\Requests\Baskets\DeleteLinesRequest;
use GetCandy\Api\Http\Requests\Baskets\ChangeQuantityRequest;

class BasketLineController extends BaseController
{
    /**
     * @var \GetCandy\Api\Core\Baskets\Factories\BasketLineFactory
     */
    protected $factory;

    /**
     * @var \GetCandy\Api\Core\Baskets\Services\BasketLineService
     */
    protected $basketLines;

    public function __construct(BasketLineFactory $factory, Request $request)
    {
        $this->factory = $factory;
        $this->basketLines = GetCandy::basketLines();
        $this->basketLines->setIncludes($request->include);
    }

    /**
     * Store one or more new basket lines, and associate them with a basket ID.
     *
     * @param  \GetCandy\Api\Http\Requests\Baskets\CreateLinesRequest  $request
     * @return array|\GetCandy\Api\Http\Resources\Baskets\BasketResource
     */
    public function store(CreateLinesRequest $request)
    {
        try {
            $basket = GetCandy::baskets()->addLines($request->all(), $request->user());
        } catch (\Illuminate\Database\QueryException $e) {
            return $this->errorUnprocessable(trans('getcandy::validation.max_qty'));
        }

        return new BasketResource($basket);
    }

    /**
     * Update a basket line's quantity.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Baskets\UpdateLineRequest  $request
     * @return \GetCandy\Api\Http\Resources\Baskets\BasketResource
     */
    public function update(string $id, UpdateLineRequest $request)
    {
        $basket = $this->basketLines->setQuantity($id, $request->quantity);

        return new BasketResource($basket);
    }

    /**
     * Increase a basketLine's quantity.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Baskets\ChangeQuantityRequest  $request
     * @return \GetCandy\Api\Http\Resources\Baskets\BasketResource
     */
    public function addQuantity(string $id, ChangeQuantityRequest $request)
    {
        $quantity = $request['quantity'] ?? 1;

        $basket = $this->basketLines->changeQuantity($id, $quantity);

        return new BasketResource($basket);
    }

    /**
     * Decrease a basketLine's quantity.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Baskets\ChangeQuantityRequest  $request
     * @return \GetCandy\Api\Http\Resources\Baskets\BasketResource
     */
    public function removeQuantity(string $id, ChangeQuantityRequest $request)
    {
        $quantity = ($request['quantity'] ?? 1) * -1;

        $basket = $this->basketLines->changeQuantity($id, $quantity);

        return new BasketResource($basket);
    }

    /**
     * Handle the request to delete a basket.
     *
     * @param  \GetCandy\Api\Http\Requests\Baskets\DeleteLinesRequest  $request
     * @return \GetCandy\Api\Http\Resources\Baskets\BasketResource
     */
    public function destroy(DeleteLinesRequest $request)
    {
        $basket = $this->basketLines->destroy($request->lines);

        return new BasketResource($basket);
    }

    public function destroyLine($id, Request $request)
    {
        // TODO: Move this to an action.
        $realId = (new BasketLine)->decodeId($id);
        BasketLine::destroy($realId);
        return $this->respondWithNoContent();
    }
}
