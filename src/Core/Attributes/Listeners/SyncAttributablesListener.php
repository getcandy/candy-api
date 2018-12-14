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
        $mapped = collect();
        $model = $event->model;

        foreach ($event->model->attribute_data as $attribute => $data) {
            $mapped->push($attribute);
        }

        // If we have a product with a family, check if they already have these associated.
        if ($model->family) {
            $existing = $model->family->attributes->pluck('handle');
            $mapped = $mapped->reject(function ($attribute) use ($existing) {
                return $existing->contains($attribute);
            });
        }
        $attributes = app('api')->attributes()->getByHandles($mapped->toArray());

        $event->model->attributes()->sync($attributes->pluck('id')->toArray());
    }
}
