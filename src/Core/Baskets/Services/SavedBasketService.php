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

class SavedBasketService extends BaseService
{
    protected $model;

    public function __construct()
    {
        $this->model = new SavedBasket;
    }

    /**
     * Update a saved basket
     *
     * @param string $id
     * @param array $payload
     * @return void
     */
    public function update($hashedId, array $data)
    {
        $basket = $this->getByHashedId($hashedId);

        $basket->name = $data['name'] ?? $basket->name;

        $basket->save();

        return $basket;
    }
}
