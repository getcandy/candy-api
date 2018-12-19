<?php

namespace GetCandy\Api\Http\Resources\Baskets;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Core\Baskets\Factories\BasketFactory;

class SavedBasketResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'name' => $this->name,
        ];
    }

    public function includes()
    {
        return [
            'basket' => $this->include(BasketResource::class, $this->getBasket()),
        ];
    }

    /**
     * Gets the basket with all it's hydrated values.
     *
     * @return \GetCandy\Api\Core\Baskets\Models\Basket
     */
    protected function getBasket()
    {
        $factory = app()->make(BasketFactory::class);

        return $factory->init($this->basket)->get();
    }
}
