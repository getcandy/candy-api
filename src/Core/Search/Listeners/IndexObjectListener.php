<?php

namespace GetCandy\Api\Core\Search\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use GetCandy\Api\Core\Search\SearchContract;
use GetCandy\Api\Core\Search\Events\IndexableSavedEvent;

class IndexObjectListener implements ShouldQueue
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
    public function handle(IndexableSavedEvent $event)
    {
        app(SearchContract::class)->indexer()->indexObject(
            $event->indexable()
        );
    }
}
