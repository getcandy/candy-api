<?php

namespace GetCandy\Api\Core\Search\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Search\Jobs\ReindexSearchJob;
use GetCandy\Api\Core\Attributes\Events\AttributeSavedEvent;

class UpdateMappingsListener implements ShouldQueue
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
    public function handle(AttributeSavedEvent $event)
    {
        ReindexSearchJob::dispatch(Product::class);
    }
}
