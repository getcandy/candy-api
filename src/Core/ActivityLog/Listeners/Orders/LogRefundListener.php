<?php

namespace GetCandy\Api\Core\ActivityLog\Listeners\Orders;

use Illuminate\Http\Request;
use GetCandy\Api\Core\Orders\Events\OrderRefundEvent;
use GetCandy\Api\Core\ActivityLog\Interfaces\ActivityLogFactoryInterface;

class LogRefundListener
{
    /**
     * The current requst.
     *
     * @var Request
     */
    protected $request;

    /**
     * The log factory.
     *
     * @var ActivityLogFactoryInterface
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
     * @param  OrderRefundEvent  $event
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
