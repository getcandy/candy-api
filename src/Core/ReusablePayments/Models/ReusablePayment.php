<?php

namespace GetCandy\Api\Core\ReusablePayments\Models;

use GetCandy;
use GetCandy\Api\Core\Scaffold\BaseModel;

class ReusablePayment extends BaseModel
{
    protected $dates = ['expires_at'];

    /**
     * The Hashid connection name for encoding the id.
     *
     * @var string
     */
    protected $hashids = 'main';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get reusable payment user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        $class = GetCandy::getUserModel();

        return $this->belongsTo($class);
    }
}
