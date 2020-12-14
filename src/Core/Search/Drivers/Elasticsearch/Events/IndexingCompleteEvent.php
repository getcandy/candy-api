<?php

namespace GetCandy\Api\Core\Search\Drivers\Elasticsearch\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IndexingCompleteEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $indexes;

    /**
     * Create a new event instance.
     *
     * @param  \App\Order  $order
     * @return void
     */
    public function __construct($indexes, $type)
    {
        $this->indexes = $indexes;
        $this->type = $type;
    }
}
