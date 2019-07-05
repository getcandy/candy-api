<?php

namespace GetCandy\Api\Http\Resources\Orders;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Users\UserResource;
use GetCandy\Api\Http\Resources\Baskets\BasketResource;
use GetCandy\Api\Http\Resources\ActivityLog\ActivityCollection;
use GetCandy\Api\Http\Resources\Transactions\TransactionCollection;

class OrderResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'display_id' => $this->display_id,
            'sub_total' => $this->sub_total,
            'type' => $this->type,
            'delivery_total' => $this->delivery_total,
            'discount_total' => $this->discount_total,
            'tax_total' => $this->tax_total,
            'shipping_preference' => $this->shipping_preference,
            'shipping_method' => $this->shipping_method,
            'order_total' => $this->order_total,
            'reference' => $this->reference,
            'customer_reference' => $this->customer_reference,
            'invoice_reference' => $this->invoice_reference,
            'vat_no' => $this->vat_no,
            'tracking_no' => $this->tracking_no,
            'dispatched_at' => $this->dispatched_at,
            'currency' => $this->currency,
            'customer_name' => $this->customer_name,
            'contact_details' => [
                'phone' => $this->contact_phone,
                'email' => $this->contact_email,
            ],
            'billing_details' => $this->billing_details,
            'shipping_details' => $this->shipping_details,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'placed_at' => $this->placed_at,
            'notes' => $this->notes,
            'meta' => $this->meta,
        ];
    }

    public function includes()
    {
        return [
            'basket' => $this->include('basket', BasketResource::class),
            'discounts' => new OrderDiscountCollection($this->whenLoaded('discounts')),
            'transactions' => new TransactionCollection($this->whenLoaded('transactions')),
            'lines' => new OrderLineCollection($this->whenLoaded('lines')),
            'shipping' => new OrderLineResource($this->whenLoaded('shipping')),
            'logs' => new ActivityCollection($this->whenLoaded('activities')),
            'user' => $this->include('user', UserResource::class),
        ];
    }
}
