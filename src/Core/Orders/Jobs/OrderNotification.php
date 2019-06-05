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
    protected $content = [];

    public function __construct(Order $order, $status, $content = [])
    {
        $this->order = $order;
        $this->status = $status;
        $this->content = $content;
    }

    public function handle()
    {
        // See if we have a mailer for this:
        $mailer = config('getcandy.orders.mailers.'.$this->status);

        if (! $mailer) {
            return;
        }

        $contactEmail = $this->order->billing_email ?: $this->order->contact_email;

        if (! $contactEmail) {
            $contactEmail = ($this->order->user ? $this->order->user->email : null);
        }

        $mailer = new $mailer($this->order);

        foreach ($this->content as $key => $value) {
            $mailer->with($key, $value);
        }
        if ($mailQueue = config('getcandy.mail.queue', null)) {
            Mail::to($contactEmail)->queue($mailer->onQueue($mailQueue));
        } else {
            Mail::to($contactEmail)->send($mailer);
        }
    }
}
