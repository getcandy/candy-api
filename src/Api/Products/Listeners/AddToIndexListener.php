<?php

namespace GetCandy\Api\Products\Listeners;

use GetCandy\Api\Search\SearchContract;
use GetCandy\Api\Products\Events\ProductCreatedEvent;

class AddToIndexListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  ProductCreatedEvent  $event
     * @return void
     */
    public function handle(ProductCreatedEvent $event)
    {
        $product = $event->product();
        app(SearchContract::class)->indexer()->indexObject($product);
    }
}
