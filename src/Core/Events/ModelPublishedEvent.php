<?php

namespace GetCandy\Api\Core\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class ModelPublishedEvent
{
    use SerializesModels;

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
