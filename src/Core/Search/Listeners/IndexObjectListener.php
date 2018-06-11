<?php

namespace GetCandy\Api\Core\Search\Listeners;

use GetCandy\Api\Core\Search\SearchContract;
use GetCandy\Api\Core\Search\Events\IndexableSavedEvent;

class IndexObjectListener
{
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
