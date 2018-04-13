<?php

namespace GetCandy\Api\Products\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use GetCandy\Api\Products\Models\Product;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class ProductViewedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $product;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function product()
    {
        return $this->product;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('products');
    }
}
