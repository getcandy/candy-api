<?php

namespace GetCandy\Api\Core\Customers\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerInvite extends BaseModel
{
    use SoftDeletes;

    /**
     * The Hashid connection name for encoding the id.
     *
     * @var string
     */
    protected $hashids = 'main';

    protected $fillable = [
        'email',
        'customer_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
