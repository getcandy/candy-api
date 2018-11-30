<?php

namespace GetCandy\Api\Http\Controllers\Baskets;

use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Baskets\SaveRequest;
use GetCandy\Api\Http\Requests\Baskets\CreateRequest;
use GetCandy\Api\Http\Requests\Baskets\DeleteRequest;
use GetCandy\Api\Http\Requests\Baskets\PutUserRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GetCandy\Api\Http\Requests\Baskets\AddDiscountRequest;
use GetCandy\Api\Http\Requests\Baskets\DeleteDiscountRequest;
use GetCandy\Api\Http\Transformers\Fractal\Baskets\BasketTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Baskets\SavedBasketTransformer;

class BasketController extends BaseController
{
    /**
     * Returns a listing of channels.
     * @return Json
     */
    public function index(Request $request)
    {
        $attributes = app('api')->baskets()->getPaginatedData($request->per_page);

        return $this->respondWithCollection($attributes, new BasketTransformer);
    }

    /**
     * Handles the request to show a channel based on it's hashed ID.
     * @param  string $id
     * @return Json
     */
    public function show($id)
    {
        try {
            $basket = app('api')->baskets()->getByHashedId($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($basket, new BasketTransformer);
    }

    public function addDiscount($basketId, AddDiscountRequest $request)
    {
        $basket = app('api')->baskets()->addDiscount($basketId, $request->coupon);

        return $this->respondWithItem($basket, new BasketTransformer);
    }

    public function deleteDiscount($basketId, DeleteDiscountRequest $request)
    {
        $basket = app('api')->baskets()->deleteDiscount($basketId, $request->discount_id);

        return $this->respondWithItem($basket, new BasketTransformer);
    }

    /**
     * Store either a new or existing basket.
     *
     * @param CreateRequest $request
     *
     * @return void
     */
    public function store(CreateRequest $request)
    {
        try {
            $basket = app('api')->baskets()->store($request->all(), $request->user());
        } catch (\Illuminate\Database\QueryException $e) {
            return $this->errorUnprocessable(trans('getcandy::validation.max_qty'));
        }

        return $this->respondWithItem($basket, new BasketTransformer);
    }

    /**
     * Saves a basket to a users account.
     *
     * @param Request $request
     * @return void
     */
    public function save($id, SaveRequest $request)
    {
        $basket = app('api')->baskets()->save($id, $request->name);

        return $this->respondWithItem($basket, new BasketTransformer);
    }

    /**
     * Handle the request to get a users saved baskets.
     *
     * @param Request $request
     * @return void
     */
    public function saved(Request $request)
    {
        $baskets = app('api')->baskets()->getSaved($request->user());

        return $this->respondWithCollection($baskets, new SavedBasketTransformer);
    }

    /**
     * Handle the request to delete a basket.
     *
     * @param string $id
     * @param DeleteRequest $request
     * @return void
     */
    public function destroy($id, DeleteRequest $request)
    {
        $result = app('api')->baskets()->destroy($request->basket);

        return $this->respondWithSuccess();
    }

    /**
     * Associate a user to a basket request.
     *
     * @param PutUserRequest $request
     *
     * @return void
     */
    public function putUser($basketId, PutUserRequest $request)
    {
        try {
            $basket = app('api')->baskets()->addUser($basketId, $request->user_id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($basket, new BasketTransformer);
    }

    public function deleteUser($basketId)
    {
        try {
            $basket = app('api')->baskets()->removeUser($basketId);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($basket, new BasketTransformer);
    }

    /**
     * Gets the basket for the current user.
     *
     * @param Request $request
     * @return void
     */
    public function current(Request $request)
    {
        $basket = app('api')->baskets()->getCurrentForUser($request->user());
        if (! $basket) {
            return $this->errorNotFound("Basket does't exist");
        }

        return $this->respondWithItem($basket, new BasketTransformer);
    }

    /**
     * Handle the request to resolve a users basket.
     *
     * @param Request $request
     * @return void
     */
    public function resolve(Request $request)
    {
        $basket = app('api')->baskets()->resolve($request->user(), $request->basket_id, $request->merge);

        return $this->respondWithItem($basket, new BasketTransformer);
    }
}
