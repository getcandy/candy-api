<?php

namespace GetCandy\Api\Http\Controllers\Baskets;

use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Baskets\SaveRequest;
use GetCandy\Api\Http\Requests\Baskets\CreateRequest;
use GetCandy\Api\Http\Requests\Baskets\DeleteRequest;
use GetCandy\Api\Core\Baskets\Factories\BasketFactory;
use GetCandy\Api\Http\Requests\Baskets\PutUserRequest;
use GetCandy\Api\Http\Requests\Baskets\AddMetaRequest;
use GetCandy\Api\Http\Resources\Baskets\BasketResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GetCandy\Api\Core\Discounts\Services\DiscountService;
use GetCandy\Api\Http\Requests\Baskets\AddDiscountRequest;
use GetCandy\Api\Http\Requests\Baskets\ClaimBasketRequest;
use GetCandy\Api\Http\Requests\Baskets\DeleteDiscountRequest;
use GetCandy\Api\Core\Baskets\Interfaces\BasketCriteriaInterface;
use GetCandy\Api\Http\Transformers\Fractal\Baskets\BasketTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Baskets\SavedBasketTransformer;

class BasketController extends BaseController
{
    /**
     * @var BasketCriteria
     */
    protected $baskets;

    /**
     * @var BasketFactory
     */
    protected $factory;

    public function __construct(BasketCriteriaInterface $baskets, BasketFactory $factory)
    {
        $this->baskets = $baskets;
        $this->factory = $factory;
    }

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

        return new BasketResource($basket);
    }

    /**
     * Handles the request to add meta data to a basket.
     * @param  string $id
     * @param  AddMetaRequest $request
     * @return Json
     */
    public function addMeta($id, AddMetaRequest $request)
    {
        try {
            $basket = app('api')->baskets()->getByHashedId($id);
            $basket->meta = array_merge($basket->meta, [$request->key => $request->value]);
            $basket->save();
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new BasketResource($basket);
    }

    /**
     * Handle the request to add a discount to a basket.
     *
     * @param string $basketId
     * @param AddDiscountRequest $request
     * @param DiscountService $discounts
     * @return BasketResource
     */
    public function addDiscount($basketId, AddDiscountRequest $request, DiscountService $discounts)
    {
        $discount = $discounts->getByCoupon($request->coupon);
        $basket = $this->baskets->id($basketId)->first();
        $factory = $this->factory->init($basket);
        $factory->lines->discount($discount->set->discount);

        $discount->uses ? $discount->increment('uses') : 1;

        if (! $basket->discount($request->coupon)) {
            $basket->discounts()->attach($discount->set->discount->id, ['coupon' => $request->coupon]);
        }

        $basket->load('discounts');

        return new BasketResource(
            $factory->get()
        );
    }

    public function deleteDiscount($basketId, DeleteDiscountRequest $request)
    {
        $basket = app('api')->baskets()->deleteDiscount($basketId, $request->discount_id);

        return new BasketResource($this->factory->init($basket->refresh())->get());
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
        // try {
            $basket = app('api')->baskets()->store($request->all(), $request->user());
        // } catch (\Illuminate\Database\QueryException $e) {
        //     return $this->errorUnprocessable(trans('getcandy::validation.max_qty'));
        // }

        return new BasketResource($basket);
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

        return new BasketResource($basket);
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
     * @deprecated 0.2.39
     * @deprecated Use claim instead, this function will be removed in 0.3.0
     * @return void
     */
    public function putUser($basketId, PutUserRequest $request)
    {
        // TODO Remove in 0.3.0
        try {
            $basket = app('api')->baskets()->addUser($basketId, $request->user_id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new BasketResource($basket);
    }

    /**
     * Associate a user to a basket request.
     *
     * @param ClaimBasketRequest $request
     *
     * @return void
     */
    public function claim($basketId, ClaimBasketRequest $request)
    {
        try {
            $basket = app('api')->baskets()->addUser($basketId, $request->user()->encodedId());
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new BasketResource($basket);
    }

    public function deleteUser($basketId)
    {
        try {
            $basket = app('api')->baskets()->removeUser($basketId);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new BasketResource($basket);
    }

    /**
     * Gets the basket for the current user.
     *
     * @param Request $request
     * @return void
     */
    public function current(Request $request)
    {
        $basket = app('api')->baskets()->getCurrentForUser($request->user(), $request->includes);
        if (! $basket) {
            return $this->errorNotFound("Basket does't exist");
        }

        return new BasketResource($basket);
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

        return new BasketResource($basket);
    }
}
