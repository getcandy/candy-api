<?php

namespace GetCandy\Api\Core\Baskets\Services;

use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Baskets\Models\BasketLine;

class BasketLineService extends BaseService
{
    /**
     * @var Basket
     */
    protected $model;

    public function __construct()
    {
        $this->model = new BasketLine();
    }

    public function variantExists($id, $variant)
    {
        $id = $this->getDecodedId($id);

        return $this->model->where('id', '=', $id)->whereHas('variant', function ($q) use ($variant) {
            $realId = app('api')->productVariants()->getDecodedId($variant);

            return $q->where('id', '=', $realId);
        })->exists();
    }
}
