<?php

namespace GetCandy\Api\Core\Search\Listeners;

use GetCandy\Api\Core\Search\Actions\IndexObjects;
use GetCandy\Api\Core\Search\Events\IndexableSavedEvent;

class IndexObjectListener
{
    /**
     * Handle the event.
     *
     * @param  \GetCandy\Api\Core\Search\Events\IndexableSavedEvent  $event
     * @return void
     */
    public function handle(IndexableSavedEvent $event)
    {
        IndexObjects::run([
            'documents' => $event->indexable(),
        ]);
    }
}
