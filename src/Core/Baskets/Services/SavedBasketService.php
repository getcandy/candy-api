<?php

namespace GetCandy\Api\Core\Baskets\Services;

use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Baskets\Models\SavedBasket;

class SavedBasketService extends BaseService
{
    protected $model;

    public function __construct()
    {
        $this->model = new SavedBasket;
    }

    /**
     * Update a saved basket.
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
