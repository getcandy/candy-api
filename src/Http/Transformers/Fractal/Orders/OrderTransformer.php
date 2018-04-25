<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Orders;

use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Users\UserTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Baskets\BasketTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Payments\TransactionTransformer;

class OrderTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'lines', 'user', 'basket', 'transactions', 'discounts', 'shipping',
    ];

    public function transform(Order $order)
    {
        $data = [
            'id' => $order->encodedId(),
            'total' => round($order->total, 2),
            'sub_total' => round($order->subTotal, 2),
            'tax' => round($order->tax, 2),
            'reference' => $order->ref,
            'invoice_reference' => $order->invoice_reference,
            'vat_no' => $order->vat_no,
            'tracking_no' => $order->tracking_no,
            'dispatched_at' => $order->dispatched_at,
            'currency' => $order->currency,
            'customer_name' => $order->customer_name,
            'contact_details' => [
                'phone' => $order->contact_phone,
                'email' => $order->contact_email,
            ],
            'billing_details' => $order->billing_details,
            'shipping_details' => $order->shipping_details,
            'status' => $order->status,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
            'placed_at' => $order->placed_at,
            'notes' => $order->notes,
        ];

        return $data;
    }

    protected function includeShipping(Order $order)
    {
        if (! $order->shipping) {
            return $this->null();
        }

        return $this->item($order->shipping, new OrderLineTransformer);
    }

    protected function includeLines(Order $order)
    {
        return $this->collection($order->lines, new OrderLineTransformer);
    }

    protected function includeBasket(Order $order)
    {
        return $this->item($order->basket, new BasketTransformer);
    }

    protected function includeDiscounts(Order $order)
    {
        return $this->collection($order->discounts, new OrderDiscountTransformer);
    }

    protected function includeUser(Order $order)
    {
        if (! $order->user) {
            return;
        }

        return $this->item($order->user, new UserTransformer);
    }

    protected function includeTransactions(Order $order)
    {
        return $this->collection($order->transactions, new TransactionTransformer);
    }
}
