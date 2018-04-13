<?php

namespace GetCandy\Api\Baskets\Services;

use Carbon\Carbon;
use GetCandy\Api\Auth\Models\User;
use GetCandy\Api\Discounts\Factory;
use GetCandy\Api\Scaffold\BaseService;
use GetCandy\Api\Baskets\Models\Basket;
use GetCandy\Api\Baskets\Models\BasketTotal;
use GetCandy\Api\Baskets\Events\BasketStoredEvent;

class BasketService extends BaseService
{
    /**
     * @var Basket
     */
    protected $model;

    public function __construct()
    {
        $this->model = new Basket();
    }

    /**
     * Gets either a new or existing basket for a user.
     *
     * @param mixed $id
     * @param mixed $user
     *
     * @return Basket
     */
    public function getBasket($id = null, $user = null)
    {
        $basket = new Basket();

        if ($id) {
            $basket = $this->getByHashedId($id);
        } elseif ($user && $userBasket = $this->getCurrentForUser($user)) {
            $basket = $userBasket;
        }

        return $basket;
    }

    /**
     * Detach a user from a basket.
     *
     * @param string $basketId
     *
     * @return Basket
     */
    public function removeUser($basketId)
    {
        $basket = $this->getByHashedId($basketId);

        $basket->user()->dissociate();

        if ($basket->discounts) {
            foreach ($basket->discounts as $discount) {
                $discountFactory = app('api')->discounts()->getFactory($discount);
                $check = (new Factory)->checkCriteria(
                    $discountFactory,
                    $basket->user,
                    $basket
                );
                if (! $check) {
                    $basket->discounts()->detach($discount);
                }
            }
        }

        $basket->save();

        return $basket;
    }

    /**
     * Add a user to a basket.
     *
     * @param string $basketId
     * @param string $userId
     *
     * @return Basket
     */
    public function addUser($basketId, $userId)
    {
        $basket = $this->getByHashedId($basketId);
        $user = app('api')->users()->getByHashedId($userId);
        $basket->user()->associate($user);
        $basket->save();

        return $basket;
    }

    /**
     * Store a basket.
     *
     * @param array $data
     *
     * @return Basket
     */
    public function store(array $data, $user = null)
    {
        $basket = $this->getBasket(
            ! empty($data['basket_id']) ? $data['basket_id'] : null,
            $user
        );

        if (empty($data['currency'])) {
            $basket->currency = app('api')->currencies()->getDefaultRecord()->code;
        } else {
            $basket->currency = $data['currency'];
        }

        $basket->save();

        if ($user && ! $basket->user) {
            $basket->user()->associate($user);
        }

        $basket->lines()->delete();

        if (! empty($data['variants'])) {
            $this->remapLines($basket, $data['variants']);
        }

        $basket->save();
        event(new BasketStoredEvent($basket));

        return $basket;
    }

    protected function remapLines($basket, $variants = [])
    {
        $service = app('api')->productVariants();

        $variants = collect($variants)->map(function ($item) use ($service) {
            $variant = $service->getByHashedId($item['id']);

            $tieredPrice = $service->getTieredPrice($variant, $item['quantity'], \Auth::user());

            if ($tieredPrice) {
                $price = $tieredPrice->amount;
            } else {
                $price = $variant->total_price;
            }

            return [
                'product_variant_id' => $variant->id,
                'quantity' => $item['quantity'],
                'total' => $item['quantity'] * $price,
            ];
        });

        $basket->lines()->createMany($variants->toArray());
    }

    /**
     * Adds a discount to a basket.
     *
     * @param string $basketId
     * @param string $coupon
     *
     * @return Basket
     */
    public function addDiscount($basketId, $coupon)
    {
        $basket = $this->getByHashedId($basketId);
        $discountCriteria = app('api')->discounts()->getByCoupon($coupon);
        $discount = $discountCriteria->set->discount;
        $discount->increment('uses');
        $basket->discounts()->attach($discount->id, ['coupon' => $coupon]);

        return $basket;
    }

    /**
     * Delete a discount.
     *
     * @param string $basketId
     * @param string $discountId
     * @return void
     */
    public function deleteDiscount($basketId, $discountId)
    {
        $basket = $this->getByHashedId($basketId);
        $discount = app('api')->discounts()->getByHashedId($discountId);

        $basket->discounts()->detach($discount);

        event(new BasketStoredEvent($basket));

        return $basket;
    }

    /**
     * Get a basket for a user.
     *
     * @param mixed $user
     *
     * @return mixed
     */
    public function getCurrentForUser($user)
    {
        if (! $user || ! is_string($user) && ! $user instanceof User) {
            return;
        }

        if (is_string($user)) {
            $user = $this->getByHashedId($user);
        }

        $basket = $user->latestBasket;

        if ($basket) {
            if ($basket->order && ! $basket->order->placed_at || ! $basket->order) {
                return $basket;
            }
        }

        return new Basket();
    }

    /**
     * Resolves a guest basket with an existing basket.
     *
     * @param User $user
     * @param string $basketId
     * @param bool $merge
     *
     * @return Basket
     */
    public function resolve($user, $basketId, $merge = true)
    {
        // Guest basket
        $basket = $this->getByHashedId($basketId);

        // User basket
        $userBasket = $user->basket;

        if ($merge) {
            return $this->merge($basket, $userBasket);
        }

        $basket->resolved_at = Carbon::now();
        $user->basket()->save($basket);
        $basket->save();

        return $basket;
    }

    /**
     * Merges two baskets.
     *
     * @param Basket $guestBasket
     * @param Basket $userBasket
     * @return Basket
     */
    public function merge($guestBasket, $userBasket)
    {
        $newLines = $guestBasket->lines;
        $overrides = $newLines->pluck('variant.id');
        $oldLines = $userBasket->lines->filter(function ($line) use ($overrides) {
            if (! $overrides->contains($line->variant->id)) {
                return $line;
            }
        });
        $guestBasket->update([
            'resolved_at' => Carbon::now(),
            'merged_id' => $userBasket->id,
        ]);
        $userBasket->lines()->delete();
        $userBasket->lines()->createMany(
            $newLines->merge($oldLines)->toArray()
        );

        return $userBasket;
    }

    /**
     * Get the totals for a basket.
     *
     * @param Basket $basket
     *
     * @return BasketTotal
     */
    public function setTotals(Basket $basket)
    {
        $factory = new Factory;
        $sets = app('api')->discounts()->parse($basket->discounts);
        $applied = $factory->getApplied($sets, \Auth::user(), null, $basket);
        $factory->applyToBasket($applied, $basket);

        return $basket;
    }
}
