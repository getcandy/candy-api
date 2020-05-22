<?php

namespace GetCandy\Api\Core\Products\Listeners;

use GetCandy\Api\Core\Products\Events\ProductCreatedEvent;
use GetCandy\Api\Core\Search\SearchContract;

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
        if (! $product->isDraft()) {
            app(SearchContract::class)->indexer()->indexObject($product);
        }
    }
}
