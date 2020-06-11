<?php

namespace GetCandy\Api\Core\Attributes\Events;

use Illuminate\Database\Eloquent\Model;

class AttributableSavedEvent
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    public $model;

    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }
}
