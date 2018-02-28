<?php

namespace GetCandy\Api\Products\Listeners;

use GetCandy\Api\Products\Events\ProductCreatedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use GetCandy\Api\Search\SearchContract;

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
