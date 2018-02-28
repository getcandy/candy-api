<?php

namespace GetCandy\Api\Associations\Models;

use GetCandy\Api\Scaffold\BaseModel;

class AssociationGroup extends BaseModel
{
    /**
     * @var string
     */
    protected $hashids = 'main';

    protected $fillable = ['association_id', 'type'];
}
