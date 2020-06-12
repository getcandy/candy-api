<?php

namespace GetCandy\Api\Core\Baskets\Events;

use GetCandy\Api\Core\Baskets\Models\Basket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BasketStoredEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var \GetCandy\Api\Core\Baskets\Models\Basket
     */
    public $basket;

    /**
     * Create a new event instance.
     *
     * @param  \GetCandy\Api\Core\Baskets\Models\Basket  $basket
     * @return void
     */
    public function __construct(Basket $basket)
    {
        $this->basket = $basket;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\PrivateChannel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('baskets');
    }
}
