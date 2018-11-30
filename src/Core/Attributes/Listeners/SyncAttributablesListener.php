<?php

namespace GetCandy\Api\Core\Attributes\Listeners;

use GetCandy\Api\Core\Attributes\Events\AttributableSavedEvent;

class SyncAttributablesListener
{
    /**
     * Handle the event.
     *
     * @param  OrderShipped  $event
     * @return void
     */
    public function handle(AttributableSavedEvent $event)
    {
        $mapped = [];
        foreach ($event->model->attribute_data as $attribute => $data) {
            $mapped[] = $attribute;
        }
        $attributes = app('api')->attributes()->getByHandles($mapped);

        $event->model->attributes()->sync($attributes->pluck('id')->toArray());
    }
}
