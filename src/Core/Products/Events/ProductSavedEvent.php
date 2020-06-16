<?php

namespace GetCandy\Api\Core\Products\Events;

use GetCandy\Api\Core\Products\Models\Product;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductSavedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var \GetCandy\Api\Core\Products\Models\Product
     */
    protected $product;

    /**
     * Create a new event instance.
     *
     * @param  \GetCandy\Api\Core\Products\Models\Product  $product
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
     * @return \Illuminate\Broadcasting\PrivateChannel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('products');
    }
}
