<?php

namespace GetCandy\Api\Core\Search\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IndexableSavedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $indexable;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Model $indexable)
    {
        $this->indexable = $indexable;
    }

    public function indexable()
    {
        return $this->indexable;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\PrivateChannel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('indexables');
    }
}
