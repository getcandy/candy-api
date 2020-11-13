<?php

namespace GetCandy\Api\Core\Products\Listeners;

use GetCandy\Api\Core\Products\Events\ProductCreatedEvent;
use GetCandy\Api\Core\Search\Actions\IndexObjects;

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
     * @param  \GetCandy\Api\Core\Products\Events\ProductCreatedEvent  $event
     * @return void
     */
    public function handle(ProductCreatedEvent $event)
    {
        $product = $event->product();
        if (! $product->isDraft()) {
            IndexObjects::run([
                'documents' => $product,
            ]);
        }
    }
}
