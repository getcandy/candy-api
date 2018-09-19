<?php

namespace GetCandy\Api\Core\Orders\Jobs;

use Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use GetCandy\Api\Core\Orders\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class OrderNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;
    protected $status;

    public function __construct(Order $order, $status)
    {
        $this->order = $order;
        $this->status = $status;
    }

    public function handle()
    {
        // See if we have a mailer for this:
        $mailer = config('getcandy.orders.mailers.'.$this->status);

        if (! $mailer) {
            return;
        }

        $contactEmail = $this->order->contact_email ?? ($this->order->user ? $this->order->user->email : null);

        if (! $contactEmail) {
            return;
        }

        Mail::to($contactEmail)->send(new $mailer($this->order));
    }
}
