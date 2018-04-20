<?php

namespace GetCandy\Api\Core\Attributes\Events;

use Illuminate\Database\Eloquent\Model;

class AttributableSavedEvent
{
    public $model;

    /**
     * Create a new event instance.
     *
     * @param  Order  $order
     * @return void
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }
}
