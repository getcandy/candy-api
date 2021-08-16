<?php

namespace GetCandy\Api\Core\Baskets\Services;

use Carbon\Carbon;
use GetCandy;
use GetCandy\Api\Core\Auth\Models\User;
use GetCandy\Api\Core\Baskets\Events\BasketStoredEvent;
use GetCandy\Api\Core\Baskets\Interfaces\BasketFactoryInterface;
use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Baskets\Models\SavedBasket;
use GetCandy\Api\Core\Discounts\Factory;
use GetCandy\Api\Core\Discounts\Models\Discount;
use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Products\Interfaces\ProductVariantInterface;
use GetCandy\Api\Core\Scaffold\BaseService;

class BasketService extends BaseService
{
    /**
     * @var \GetCandy\Api\Core\Baskets\Models\Basket
     */
    protected $model;

    /**
     * The basket factory.
     *
     * @var \GetCandy\Api\Core\Baskets\Interfaces\BasketFactoryInterface
     */
    protected $factory;

    /**
     * The variant factory.
     *
     * @var \GetCandy\Api\Core\Products\Interfaces\ProductVariantInterface
     */
    protected $variantFactory;

    public function __construct(
        BasketFactoryInterface $factory,
        ProductVariantInterface $variantFactory
    ) {
        $this->model = new Basket();
        $this->factory = $factory;
        $this->variantFactory = $variantFactory;
    }

    /**
     * Gets either a new or existing basket for a user.
     *
     * @param  null|string  $id
     * @param  null|\Illuminate\Database\Eloquent\Model  $user
     * @return \GetCandy\Api\Core\Baskets\Models\Basket
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
            $basket->currency = GetCandy::currencies()->getDefaultRecord()->code;
        }

        $basket->save();

        return $basket;
    }

    /**
     * Get a basket by it's hashed ID.
     *
     * @param  string  $id
     * @return \GetCandy\Api\Core\Baskets\Models\Basket
     */
    public function getByHashedId($id)
    {
        $id = $this->model->decodeId($id);
        $basket = $this->model->with([
            'user',
            'order',
            'discounts.rewards',
            'lines.basket',
            'lines.variant',
            'lines.variant.tax',
            'lines.variant.tiers',
            'lines.variant.image.transforms',
            'lines.variant.product',
            'lines.variant.product.assets',
            'lines.variant.product.assets.transforms',
            'lines.variant.product.routes',
            'lines.variant.customerPricing',
        ])->findOrFail($id);

        return $this->factory->init($basket)->get();
    }

    /**
     * Get basket for an order.
     *
     * @param  \GetCandy\Api\Core\Orders\Models\Order  $order
     * @return \GetCandy\Api\Core\Baskets\Models\Basket
     */
    public function getForOrder(Order $order)
    {
        if (! $order->basket) {
            return;
        }

        return $this->factory->init($order->basket)->get();
    }

    /**
     * Detach a user from a basket.
     *
     * @param  string  $basketId
     * @return \GetCandy\Api\Core\Baskets\Models\Basket
     */
    public function removeUser($basketId)
    {
        $basket = $this->getByHashedId($basketId);

        $basket->user()->dissociate();

        if ($basket->discounts) {
            foreach ($basket->discounts as $discount) {
                $discountFactory = GetCandy::discounts()->getFactory($discount);
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
     * @param  string  $basketId
     * @param  string  $userId
     * @return \GetCandy\Api\Core\Baskets\Models\Basket
     */
    public function addUser($basketId, $userId)
    {
        $basket = $this->getByHashedId($basketId);
        $user = GetCandy::users()->getByHashedId($userId);
        $basket->user()->associate($user);
        $basket->save();

        return $basket;
    }

    /**
     * Store a basket.
     *
     * @param  array  $data
     * @param  null|\Illuminate\Database\Eloquent\Model  $user
     * @return \GetCandy\Api\Core\Baskets\Models\Basket
     */
    public function store(array $data, $user = null)
    {
        $basket = $this->getBasket(
            ! empty($data['basket_id']) ? $data['basket_id'] : null,
            $user
        );

        $basket = $this->setupBasket($basket, $data);

        $basket->lines()->delete();

        return $this->storeAndUpdateBasket($basket, $data);
    }

    /**
     * Add new lines to a basket, without remapping the existing lines.
     *
     * @param  array  $data
     * @param  null|\Illuminate\Database\Eloquent\Model  $user
     * @return \GetCandy\Api\Core\Baskets\Models\Basket
     */
    public function addLines(array $data, $user = null)
    {
        $basket = $this->getBasket(
            ! empty($data['basket_id']) ? $data['basket_id'] : null,
            $user
        );

        $this->setupBasket($basket, $data);

        return $this->storeAndUpdateBasket($basket, $data);
    }

    /**
     * @param  \GetCandy\Api\Core\Baskets\Models\Basket  $basket
     * @param  array  $data
     * @return \GetCandy\Api\Core\Baskets\Models\Basket
     */
    protected function setupBasket(Basket $basket, array $data)
    {
        if (isset($data['meta'])) {
            $basket->meta = $data['meta'];
        }

        if (isset($data['currency'])) {
            $basket->currency = $data['currency'];
        }
        if (is_null($basket->currency)) {
            $basket->currency = GetCandy::currencies()->getDefaultRecord()->code;
        }

        return $basket;
    }

    /**
     * @param  \GetCandy\Api\Core\Baskets\Models\Basket  $basket
     * @param  array  $data
     * @return \GetCandy\Api\Core\Baskets\Models\Basket
     */
    protected function storeAndUpdateBasket(Basket $basket, array $data)
    {
        if (! empty($data['variants'])) {
            $this->remapLines($basket, $data['variants']);
        }
        $basket->load([
            'lines',
            'lines.variant.product.routes',
            'lines.variant.image.transforms',
            'lines.variant.product.assets.transforms',
        ]);

        $discounts = Discount::all();

        $eligible = [];

        foreach ($discounts as $discount) {
            foreach ($discount->items as $item) {
                if ($item->check($basket->user, $basket)) {
                    $eligible[] = $discount->id;
                }
            }
        }

        $basket->discounts()->sync($eligible);

        $basket = $this->factory->init($basket)->get();

        $basket->save();
        event(new BasketStoredEvent($basket));

        return $basket;
    }

    /**
     * Saves a basket with a name.
     *
     * @param  string  $basketId
     * @param  string  $name
     * @return \GetCandy\Api\Core\Baskets\Models\Basket
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
     * @param  \Illuminate\Database\Eloquent\Model  $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSaved($user)
    {
        return $user->savedBaskets;
    }

    protected function remapLines($basket, $variants = [])
    {
        $service = GetCandy::productVariants();

        $collectedVariants = [];

        // Collect variants with the same ID and add up their quantities
        collect($variants)->each(function ($item) use (&$collectedVariants, $service) {
            $variant = $this->variantFactory
                ->init($service->getByHashedId($item['id']))
                ->get($item['quantity']);

            if (array_key_exists($variant->id, $collectedVariants)) {
                $collectedVariants[$variant->id]['quantity'] += $item['quantity'];

                return;
            }

            $collectedVariants[$variant->id] = [
                'product_variant_id' => $variant->id,
                'quantity' => $item['quantity'],
                'total' => $item['quantity'] * $variant->price,
                'meta' => $item['meta'] ?? [],
            ];
        });

        // If a basket line with this variant already exists, increase that instead
        $basket->lines->map(function ($line) use (&$collectedVariants) {
            $variant_id = $line->product_variant_id;

            if (array_key_exists($variant_id, $collectedVariants)) {
                $line->quantity += $collectedVariants[$variant_id]['quantity'];
                unset($collectedVariants[$variant_id]);
            }

            $line->save();
        });

        $basket->lines()->createMany($collectedVariants);
    }

    /**
     * Adds a discount to a basket.
     *
     * @param  string  $basketId
     * @param  string  $coupon
     * @return \GetCandy\Api\Core\Baskets\Models\Basket
     */
    public function addDiscount($basketId, $coupon)
    {
        $basket = $this->getByHashedId($basketId);
        $discountCriteria = GetCandy::discounts()->getByCoupon($coupon);

        $discount = $discountCriteria->set->discount;
        $discount->uses ? $discount->increment('uses') : 1;
        $basket->discounts()->attach($discount->id, ['coupon' => $coupon]);

        return $basket;
    }

    /**
     * Delete a discount.
     *
     * @param  string  $basketId
     * @param  string  $discountId
     * @return \GetCandy\Api\Core\Baskets\Models\Basket
     */
    public function deleteDiscount($basketId, $discountId)
    {
        $id = $this->model->decodeId($basketId);
        $basket = $this->model->with([
            'user',
            'order',
            'discounts.rewards',
            'lines.basket',
            'lines.variant',
            'lines.variant.tax',
            'lines.variant.tiers',
            'lines.variant.product',
            'lines.variant.product.assets',
            'lines.variant.product.assets.transforms',
            'lines.variant.product.routes',
            'lines.variant.customerPricing',
        ])->findOrFail($id);

        $discount = GetCandy::discounts()->getByHashedId($discountId);

        $basket->discounts()->detach($discount);

        event(new BasketStoredEvent($basket));

        return $basket;
    }

    /**
     * Get a basket for a user.
     *
     * @param  string|\Illuminate\Database\Eloquent\Model  $user
     * @param  array  $includes
     * @return \GetCandy\Api\Core\Baskets\Models\Basket
     */
    public function getCurrentForUser($user, $includes = [])
    {
        if (! $user) {
            return;
        }

        if (is_string($user)) {
            $user = $this->getByHashedId($user);
        }

        if ($user->latestBasket) {
            $basket = $user->latestBasket->load($includes ?? []);
        }

        if (! empty($basket)) {
            if (($basket->order && ! $basket->order->placed_at) || (! $basket->order && $basket->doesntHave('savedBasket'))) {
                return $this->factory->init($basket)->get();
            }
        }

        return $this->factory->init(new Basket())->get();
    }

    /**
     * Resolves a guest basket with an existing basket.
     *
     * @param  \Illuminate\Database\Eloquent\Model $user
     * @param  string  $basketId
     * @param  bool  $merge
     * @return \GetCandy\Api\Core\Baskets\Models\Basket
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
     * @param  \GetCandy\Api\Core\Baskets\Models\Basket  $guestBasket
     * @param  \GetCandy\Api\Core\Baskets\Models\Basket  $userBasket
     * @return \GetCandy\Api\Core\Baskets\Models\Basket
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

        // Need to determine whether the basket was changed.
        $oldProducts = $guestBasket->lines->mapWithKeys(function ($l) {
            return [$l->variant->sku => $l->quantity];
        })->toArray();

        $currentProducts = $userBasket->lines->mapWithKeys(function ($l) {
            return [$l->variant->sku => $l->quantity];
        })->toArray();

        $userBasket->lines()->delete();
        $userBasket->lines()->createMany(
            $newLines->merge($oldLines)->filter(function ($line) {
                return $line->variant->availableProduct;
            })->toArray()
        );

        return $this->factory->init($userBasket)->changed(
            ! empty($oldProducts) ? ! ($currentProducts === $oldProducts) : false
        )->get();
    }

    /**
     * Delete a basket.
     *
     * @param  string|\GetCandy\Api\Core\Baskets\Models\Basket  $basket
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
