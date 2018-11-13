<?php

namespace GetCandy\Api\Core\Baskets\Services;

use Carbon\Carbon;
use GetCandy\Api\Core\Auth\Models\User;
use GetCandy\Api\Core\Discounts\Factory;
use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Baskets\Models\SavedBasket;
use GetCandy\Api\Core\Baskets\Events\BasketStoredEvent;
use GetCandy\Api\Core\Baskets\Interfaces\BasketInterface;
use GetCandy\Api\Core\Products\Interfaces\ProductVariantInterface;

class BasketService extends BaseService
{
    /**
     * @var Basket
     */
    protected $model;

    /**
     * The basket factory.
     *
     * @var BasketFactoryInterface
     */
    protected $factory;

    /**
     * The variant factory.
     *
     * @var ProductVariantInterface
     */
    protected $variantFactory;

    public function __construct(
        BasketInterface $factory,
        ProductVariantInterface $variantFactory
    ) {
        $this->model = new Basket();
        $this->factory = $factory;
        $this->variantFactory = $variantFactory;
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
            $basketId = $this->getDecodedId($id);
            $basket = Basket::find($basketId);
        } elseif ($user && $userBasket = $this->getCurrentForUser($user)) {
            $basket = $userBasket;
        }
        if ($user && ! $basket->user) {
            $basket->user()->associate($user);
        }

        if (! $basket->currency) {
            $basket->currency = app('api')->currencies()->getDefaultRecord()->code;
        }

        $basket->save();

        return $basket;
    }

    /**
     * Get a basket by it's hashed ID.
     *
     * @param string $id
     * @return BasketFactory
     */
    public function getByHashedId($id)
    {
        $id = $this->model->decodeId($id);
        $basket = $this->model->with([
            'user',
            'order',
            'lines.basket',
            'lines.variant',
            'lines.variant.tax',
            'lines.variant.tiers',
            'lines.variant.product',
            'lines.variant.customerPricing',
        ])->findOrFail($id);

        return $this->factory->init($basket)->get();
    }

    /**
     * Get basket for an order.
     *
     * @param Order $order
     * @return Basket
     */
    public function getForOrder(Order $order)
    {
        return $this->factory->init($order->basket)->get();
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

        $basket->lines()->delete();

        if (! empty($data['variants'])) {
            $this->remapLines($basket, $data['variants']);
        }
        $basket->load('lines');
        $basket = $this->factory->init($basket)->get();

        $basket->save();
        event(new BasketStoredEvent($basket));

        return $basket;
    }

    /**
     * Saves a basket with a name.
     *
     * @param string $basketId
     * @param string $name
     *
     * @return Basket
     */
    public function save($basketId, $name)
    {
        $savedBasket = new SavedBasket;
        $savedBasket->name = $name;

        // Get the original basket
        $basket = $this->getByHashedId($basketId);

        // Clone the basket
        $clone = $this->factory->init($basket)->clone();

        $savedBasket->basket()->associate($clone);

        $savedBasket->save();

        return $this->factory->init($clone)->get();
    }

    /**
     * Get a users saved baskets.
     *
     * @param mixed $user
     * @return void
     */
    public function getSaved($user)
    {
        return $user->savedBaskets;
    }

    protected function remapLines($basket, $variants = [])
    {
        $service = app('api')->productVariants();

        $variants = collect($variants)->map(function ($item) use ($service, $basket) {
            $variant = $this->variantFactory->init(
                $service->getByHashedId($item['id'])
            )->get($item['quantity']);

            return [
                'product_variant_id' => $variant->id,
                'quantity' => $item['quantity'],
                'total' => $item['quantity'] * $variant->price,
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
        $discount->uses ? $discount->increment('uses') : 1;
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
        if (! $user) {
            return;
        }

        if (is_string($user)) {
            $user = $this->getByHashedId($user);
        }

        $basket = $user->latestBasket;

        if ($basket) {
            if ($basket->order && ! $basket->order->placed_at || ! $basket->order) {
                return $this->factory->init($basket)->get();
            }
        }

        return $this->factory->init(new Basket())->get();
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
        $userBasket = $user->latestBasket;

        if ($merge && $userBasket) {
            $basket = $this->merge($basket, $userBasket);
        }

        $basket->user_id = $user->id;
        $basket->save();

        $basket->load('lines');

        return  $this->factory->init($basket)->get();
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

        return $this->factory->init($userBasket)->get();
    }

    /**
     * Delete a basket.
     *
     * @param mixed $basket
     * @return bool
     */
    public function destroy($basket)
    {
        if (is_string($basket)) {
            $basket = $this->getByHashedId($basket);
        }

        // Don't delete basket with an order attached.
        if ($basket->order) {
            return false;
        }

        // Delete any lines.
        $basket->lines()->delete();
        $basket->discounts()->delete();
        $basket->savedBasket()->delete();

        return $basket->delete();
    }
}
