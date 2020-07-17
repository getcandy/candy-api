<?php

namespace GetCandy\Api\Http\Controllers\Baskets;

use GetCandy;
use GetCandy\Api\Core\Baskets\Factories\BasketFactory;
use GetCandy\Api\Core\Baskets\Interfaces\BasketCriteriaInterface;
use GetCandy\Api\Core\Discounts\Services\DiscountService;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Baskets\AddDiscountRequest;
use GetCandy\Api\Http\Requests\Baskets\AddMetaRequest;
use GetCandy\Api\Http\Requests\Baskets\ClaimBasketRequest;
use GetCandy\Api\Http\Requests\Baskets\CreateRequest;
use GetCandy\Api\Http\Requests\Baskets\DeleteDiscountRequest;
use GetCandy\Api\Http\Requests\Baskets\DeleteRequest;
use GetCandy\Api\Http\Requests\Baskets\PutUserRequest;
use GetCandy\Api\Http\Requests\Baskets\SaveRequest;
use GetCandy\Api\Http\Resources\Baskets\BasketCollection;
use GetCandy\Api\Http\Resources\Baskets\BasketResource;
use GetCandy\Api\Http\Resources\Baskets\SavedBasketCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class BasketController extends BaseController
{
    /**
     * @var \GetCandy\Api\Core\Baskets\Interfaces\BasketCriteriaInterface
     */
    protected $baskets;

    /**
     * @var \GetCandy\Api\Core\Baskets\Factories\BasketFactory
     */
    protected $factory;

    public function __construct(BasketCriteriaInterface $baskets, BasketFactory $factory)
    {
        $this->baskets = $baskets;
        $this->factory = $factory;
    }

    /**
     * Returns a listing of baskets.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \GetCandy\Api\Http\Resources\Baskets\BasketCollection
     */
    public function index(Request $request)
    {
        $paginator = GetCandy::baskets()->getPaginatedData($request->per_page);

        return new BasketCollection($paginator);
    }

    /**
     * Handles the request to show a basket based on it's hashed ID.
     *
     * @param  string  $id
     * @return array|\GetCandy\Api\Http\Resources\Baskets\BasketResource
     */
    public function show($id)
    {
        try {
            $basket = GetCandy::baskets()->getByHashedId($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new BasketResource($basket);
    }

    /**
     * Handles the request to add meta data to a basket.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Baskets\AddMetaRequest  $request
     * @return array|\GetCandy\Api\Http\Resources\Baskets\BasketResource
     */
    public function addMeta($id, AddMetaRequest $request)
    {
        try {
            $basket = GetCandy::baskets()->getByHashedId($id);
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
     * @param  string  $basketId
     * @param  \GetCandy\Api\Http\Requests\Baskets\AddDiscountRequest  $request
     * @param  \GetCandy\Api\Core\Discounts\Services\DiscountService  $discounts
     * @return \GetCandy\Api\Http\Resources\Baskets\BasketResource
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
        $basket = GetCandy::baskets()->deleteDiscount($basketId, $request->discount_id);

        return new BasketResource($this->factory->init($basket->refresh())->get());
    }

    /**
     * Store either a new or existing basket.
     *
     * @param  \GetCandy\Api\Http\Requests\Baskets\CreateRequest  $request
     * @return \GetCandy\Api\Http\Resources\Baskets\BasketResource
     */
    public function store(CreateRequest $request)
    {
        // try {
        $basket = GetCandy::baskets()->store($request->all(), $request->user());
        // } catch (\Illuminate\Database\QueryException $e) {
        //     return $this->errorUnprocessable(trans('getcandy::validation.max_qty'));
        // }

        return new BasketResource($basket);
    }

    /**
     * Saves a basket to a users account.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Baskets\SaveRequest  $request
     * @return \GetCandy\Api\Http\Resources\Baskets\BasketResource
     */
    public function save($id, SaveRequest $request)
    {
        $basket = GetCandy::baskets()->save($id, $request->name);

        return new BasketResource($basket);
    }

    /**
     * Handle the request to get a user's saved baskets.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \GetCandy\Api\Http\Resources\Baskets\SavedBasketCollection
     */
    public function saved(Request $request)
    {
        $baskets = GetCandy::baskets()->getSaved($request->user());

        return new SavedBasketCollection($baskets);
    }

    /**
     * Handle the request to delete a basket.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Baskets\DeleteRequest  $request
     * @return array
     */
    public function destroy($id, DeleteRequest $request)
    {
        GetCandy::baskets()->destroy($request->basket);

        return $this->respondWithSuccess();
    }

    /**
     * Associate a user to a basket request.
     *
     * @param  string  $basketId
     * @param  \GetCandy\Api\Http\Requests\Baskets\PutUserRequest  $request
     * @return array|\GetCandy\Api\Http\Resources\Baskets\BasketResource
     *
     * @deprecated 0.2.39
     * @deprecated Use claim instead, this function will be removed in 0.3.0
     */
    public function putUser($basketId, PutUserRequest $request)
    {
        // TODO Remove in 0.3.0
        try {
            $basket = GetCandy::baskets()->addUser($basketId, $request->user_id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new BasketResource($basket);
    }

    /**
     * Associate a user to a basket request.
     *
     * @param  string  $basketId
     * @param  \GetCandy\Api\Http\Requests\Baskets\ClaimBasketRequest  $request
     * @return array|\GetCandy\Api\Http\Resources\Baskets\BasketResource
     */
    public function claim($basketId, ClaimBasketRequest $request)
    {
        try {
            $basket = GetCandy::baskets()->addUser($basketId, $request->user()->encodedId());
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new BasketResource($basket);
    }

    public function deleteUser($basketId)
    {
        try {
            $basket = GetCandy::baskets()->removeUser($basketId);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new BasketResource($basket);
    }

    /**
     * Gets the basket for the current user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \GetCandy\Api\Http\Resources\Baskets\BasketResource
     */
    public function current(Request $request)
    {
        $basket = GetCandy::baskets()->getCurrentForUser($request->user(), $request->includes);
        if (! $basket) {
            return $this->errorNotFound("Basket does't exist");
        }

        return new BasketResource($basket);
    }

    /**
     * Handle the request to resolve a users basket.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \GetCandy\Api\Http\Resources\Baskets\BasketResource
     */
    public function resolve(Request $request)
    {
        $basket = GetCandy::baskets()->resolve($request->user(), $request->basket_id, $request->merge);

        return new BasketResource($basket);
    }
}
