<?php

namespace GetCandy\Api\Core\Payments\Models;

use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Scaffold\BaseModel;
use Spatie\Activitylog\Traits\LogsActivity;

class Transaction extends BaseModel
{
    use LogsActivity;

    protected static $logAttributes = ['transaction_id'];

    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
    protected $hashids = 'order';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
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
