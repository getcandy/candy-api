<?php

namespace GetCandy\Api\Core\Products\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use GetCandy\Api\Core\Search\SearchContract;
use GetCandy\Api\Core\Products\Events\ProductCreatedEvent;

class AddToIndexListener implements ShouldQueue
{
    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    public $queue = 'indexers';

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
