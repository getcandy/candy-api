<?php

namespace GetCandy\Api\Core\Baskets\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use GetCandy\Api\Core\Baskets\Models\Basket;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class BasketFetchedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $basket;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Basket $basket)
    {
        $this->basket = $basket;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('baskets');
    }
}
