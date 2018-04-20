<?php

namespace GetCandy\Api\Core\Associations\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class AssociationGroup extends BaseModel
{
    /**
     * @var string
     */
    protected $hashids = 'main';

    protected $fillable = ['association_id', 'type'];
}
