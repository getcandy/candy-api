<?php

namespace GetCandy\Api\Core\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class ModelPublishedEvent
{
    use SerializesModels;

    public $draft;

    public $parent;

    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     *
     * @return void
     */
    public function __construct(Model $draft, Model $parent)
    {
        $this->draft = $draft;
        $this->parent = $parent;
    }
}
