<?php

namespace GetCandy\Api\Core\Reports\Models;

use GetCandy;
use GetCandy\Api\Core\Scaffold\BaseModel;

class ReportExport extends BaseModel
{
    /**
     * @var array
     */
    protected $dates = ['started_at', 'completed_at'];

    /**
     * The Hashid connection name for enconding the id.
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

    public function user()
    {
        return $this->belongsTo(GetCandy::getUserModel());
    }
}
