<?php

namespace GetCandy\Api\Core\Categories\Events;

use GetCandy\Api\Core\Categories\Models\Category;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CategoryStoredEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var \GetCandy\Api\Core\Categories\Models\Category
     */
    protected $category;

    /**
     * Create a new event instance.
     *
     * @param  \GetCandy\Api\Core\Categories\Models\Category  $category
     * @return void
     */
    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    public function category()
    {
        return $this->category;
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
