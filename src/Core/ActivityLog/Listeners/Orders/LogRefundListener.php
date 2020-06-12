<?php

namespace GetCandy\Api\Core\ActivityLog\Listeners\Orders;

use GetCandy\Api\Core\ActivityLog\Interfaces\ActivityLogFactoryInterface;
use GetCandy\Api\Core\Orders\Events\OrderRefundEvent;
use Illuminate\Http\Request;

class LogRefundListener
{
    /**
     * The current requst.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * The log factory.
     *
     * @var \GetCandy\Api\Core\ActivityLog\Interfaces\ActivityLogFactoryInterface
     */
    protected $factory;

    public function __construct(Request $request, ActivityLogFactoryInterface $factory)
    {
        $this->request = $request;
        $this->factory = $factory;
    }

    /**
     * Handle the event.
     *
     * @param  \GetCandy\Api\Core\Orders\Events\OrderRefundEvent  $event
     * @return void
     */
    public function handle(OrderRefundEvent $event)
    {
        $this->factory->against($event->order)
            ->as($this->request->user())
            ->with([
                'transaction_id' => $event->transaction->transaction_id,
            ])
            ->action('refund')
            ->log('system');
    }
}
