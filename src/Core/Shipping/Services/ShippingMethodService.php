<?php

namespace GetCandy\Api\Core\Shipping\Services;

use Illuminate\Pipeline\Pipeline;
use GetCandy\Api\Core\Shipping\Events;
use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Shipping\ShippingCalculator;
use GetCandy\Api\Core\Baskets\Services\BasketService;
use GetCandy\Api\Core\Shipping\Models\ShippingMethod;
use GetCandy\Api\Core\Attributes\Events\AttributableSavedEvent;
use GetCandy\Api\Core\Shipping\Events\ShippingMethodsFetchedEvent;

class ShippingMethodService extends BaseService
{

    protected $pipes = [];

    /**
     * The basket service.
     *
     * @var BasketService
     */
    protected $baskets;

    public function __construct(BasketService $baskets)
    {
        $this->model = new ShippingMethod();
        $this->baskets = $baskets;
        $this->pipes = config('getcandy.pipelines.shipping_methods', []);
    }

    /**
     * Create a shipping method.
     *
     * @param array $data
     *
     * @return ShippingMethod
     */
    public function create(array $data)
    {
        $shipping = new ShippingMethod;
        $shipping->attribute_data = $data;
        $shipping->type = $data['type'];

        $shipping->save();

        if (! empty($data['channels']['data'])) {
            $shipping->channels()->sync(
                $this->getChannelMapping($data['channels']['data'])
            );
        }

        event(new AttributableSavedEvent($shipping));

        return $shipping;
    }

    /**
     * Update a shipping method.
     *
     * @param string $id
     * @param array $data
     *
     * @return ShippingMethod
     */
    public function update($id, array $data)
    {
        $shipping = $this->getByHashedId($id);
        $shipping->attribute_data = $data['attribute_data'];
        $shipping->type = $data['type'];

        if (! empty($data['channels']['data'])) {
            $shipping->channels()->sync(
                $this->getChannelMapping($data['channels']['data'])
            );
        }

        $shipping->save();

        return $shipping;
    }

    public function all()
    {
        return $this->model->with([
          'zones',
          'users',
          'prices',
          'channels',
        ])->channel()->get();
    }

    /**
     * Gets shipping methods for an order.
     *
     * @param string $orderId
     *
     * @return ArrayCollection
     */
    public function getForOrder($orderId)
    {
        // Get the zones for this order...
        $order = app('api')->orders()->getByHashedId($orderId);
        $basket = $this->baskets->getForOrder($order);

        $zones = app('api')->shippingZones()->getByCountryName($order->shipping_details['country']);
        $basket = $order->basket;
        $calculator = new ShippingCalculator(app());

        $options = [];


        foreach ($zones as $zone) {
            foreach ($zone->methods as $index => $method) {
                if ($method->type == $order->type) {
                    $options[$index] = $method->prices->first()->load('method');
                }
                $option = $calculator->with($method)->calculate($order);
                if (! $option) {
                    continue;
                }
                $option->load(['method']);
                if (is_array($option)) {
                    $options = array_merge($options, $option);
                } else {
                    $options[$option->method->id] = $option;
                }
            }
        }

        $options = collect($options);

        if ($basket->hasExclusions) {
            $exclusions = collect();
            $basket->lines->each(function ($l) use ($exclusions) {
                if (!$l->variant) {
                    return;
                }
                $exclusions->push(
                    $l->variant->product->exclusions
                );
            });

            $options = $options->reject(function ($option) use ($exclusions) {
                return $exclusions->flatten()->contains('shipping_zone_id', $option->shipping_zone_id);
            });
        }
        // dd($options);

        return app(Pipeline::class)->send([collect($options), $order])->through($this->pipes)->then(function ($options) {
            return collect($options[0])->unique('shipping_method_id');
        });
    }

    /**
     * Updates zones for a shipping method.
     *
     * @param string $methodId
     * @param array $data
     *
     * @return ShippingMethod
     */
    public function updateZones($methodId, $data = [])
    {
        $method = $this->getByHashedId($methodId);

        $method->zones()->detach();

        if (! empty($data['zones'])) {
            $method->zones()->attach(
                app('api')->shippingZones()->getDecodedIds($data['zones'])
            );
        }

        return $method;
    }

    /**
     * Update users for a shipping method.
     *
     * @param string $methodId
     * @param array $users
     *
     * @return ShippingMethod
     */
    public function updateUsers($methodId, $users = [])
    {
        $method = $this->getByHashedId($methodId);

        $method->users()->detach();

        $method->users()->attach(
            app('api')->users()->getDecodedIds($users)
        );

        return $method;
    }

    /**
     * Remove a user from a shipping method.
     *
     * @param string $methodId
     * @param string $userId
     *
     * @return ShippingMethod
     */
    public function deleteUser($methodId, $userId)
    {
        $user = app('api')->users()->getDecodedId($userId);
        $method = $this->getByHashedId($methodId);
        $method->users()->detach($user);

        return $method;
    }

    public function delete($methodId)
    {
        $method = $this->getByHashedId($methodId);

        $method->zones()->detach();
        $method->users()->detach();

        foreach ($method->prices as $price) {
            $price->customerGroups()->detach();
            $price->delete();
        }

        $method->channels()->detach();
        $method->delete();

        return true;
    }
}
