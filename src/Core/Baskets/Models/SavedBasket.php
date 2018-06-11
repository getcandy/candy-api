<?php

namespace GetCandy\Api\Core\Baskets\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;


class SavedBasket extends BaseModel
{
    public function basket()
    {
        return $this->belongsTo(Basket::class);
    }
}
