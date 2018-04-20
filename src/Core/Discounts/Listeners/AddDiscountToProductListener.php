<?php

namespace GetCandy\Api\Core\Discounts\Listeners;

use GetCandy\Api\Core\Discounts\Factory;
use GetCandy\Api\Core\Products\Events\ProductViewedEvent;

class AddDiscountToProductListener
{
    protected $factory;

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Handle the event.
     *
     * @param  OrderShipped  $event
     * @return void
     */
    public function handle(ProductViewedEvent $event)
    {
        $product = $event->product();
        $discounts = app('api')->discounts()->get();
        $sets = app('api')->discounts()->parse($discounts);

        $product->max_price = 0;
        $product->min_price = 0;

        foreach ($product->variants as $variant) {
            $product->max_price = $variant->price > $product->max_price ? $variant->price : $product->max_price;
            if ($product->min_price) {
                $product->min_price = $variant->price < $product->min_price ? $variant->price : $product->min_price;
            } else {
                $product->min_price = $product->max_price;
            }
        }

        $applied = $this->factory->getApplied($sets, \Auth::user(), $product);

        $this->factory->apply($applied, $product);
    }
}
