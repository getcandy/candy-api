<?php

namespace GetCandy\Api\Search\Listeners;

use GetCandy\Api\Search\SearchContract;
use GetCandy\Api\Search\Events\IndexableSavedEvent;

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
