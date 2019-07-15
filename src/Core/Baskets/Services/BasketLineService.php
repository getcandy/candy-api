<?php

namespace GetCandy\Api\Core\Baskets\Services;

use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Baskets\Models\BasketLine;
use GetCandy\Api\Core\Baskets\Interfaces\BasketFactoryInterface;

class BasketLineService extends BaseService
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
     * @var string
     */
    protected $includes = [];

    public function __construct(BasketFactoryInterface $factory)
    {
        $this->model = new BasketLine();
        $this->factory = $factory;
    }

    /**
     * @param null|string $includes
     */
    public function setIncludes(?string $includes)
    {
        $this->includes = $includes ?? [];
    }

    /**
     * @param $id
     * @param $variant
     * @return mixed
     */
    public function variantExists($id, $variant)
    {
        $id = $this->getDecodedId($id);

        return $this->model->where('id', '=', $id)->whereHas('variant', function ($q) use ($variant) {
            $realId = app('api')->productVariants()->getDecodedId($variant);

            return $q->where('id', '=', $realId);
        })->exists();
    }

    /**
     * @param string $id
     * @param int $quantity
     * @return Basket
     */
    public function setQuantity(string $id, int $quantity)
    {
        $id = $this->getDecodedId($id);

        $basketLine = $this->model->where('id', '=', $id)->with('basket')->firstOrFail();

        $this->saveQuantity($basketLine, $quantity);

        $basket = $basketLine->basket->load($this->includes);
        $basket = $this->factory->init($basket)->get();

        return $basket;
    }

    /**
     * @param string $id
     * @param int $quantity
     * @param array $includes
     * @return Basket
     */
    public function changeQuantity(string $id, int $quantity)
    {
        $id = $this->getDecodedId($id);

        $basketLine = $this->model->where('id', '=', $id)->with('basket')->firstOrFail();

        $this->saveQuantity($basketLine, $basketLine->quantity + $quantity);

        $basket = $basketLine->basket->load($this->includes);
        $basket = $this->factory->init($basket)->get();

        return $basket;
    }

    /**
     * @param array $lines
     * @return Basket
     */
    public function destroy(array $lines)
    {
        $basket = null;

        collect($lines)->each(function ($basketLine) use (&$basket) {
            $id = $this->getDecodedId($basketLine['id']);

            $basketLine = $this->model->where('id', '=', $id)->with('basket')->firstOrFail();

            $basket = $basketLine->basket;
            $basketLine->delete();
        });

        // @todo Not sure what to return here, as theoretically these basket lines
        // could be for different baskets...?
        $basket = $basket->load($this->includes);
        $basket = $this->factory->init($basket)->get();

        return $basket;
    }

    /**
     * @param BasketLine $basketLine
     * @param int $quantity
     * @return bool
     */
    protected function saveQuantity(BasketLine $basketLine, int $quantity)
    {
        $basketLine->quantity = $quantity;

        if ($basketLine->quantity <= 0) {
            $basketLine->delete();

            return true;
        }

        return $basketLine->save();
    }
}
