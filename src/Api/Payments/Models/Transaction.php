<?php

namespace GetCandy\Api\Payments\Models;

use GetCandy\Api\Orders\Models\Order;
use GetCandy\Api\Scaffold\BaseModel;

class Transaction extends BaseModel
{
    protected $hashids = 'order';

    protected $fillable = [
        'merchant', 'success', 'status',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class)->withoutGlobalScope('open');
    }

    public function scopeCharged($query)
    {
        return $query->where('status', '!=', 'voided')->where('status', '!=', 'refunded')->where('success', '=', true);
    }
}
