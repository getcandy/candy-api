<?php

namespace GetCandy\Api\Core\Associations\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class AssociationGroup extends BaseModel
{
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
    protected $fillable = ['association_id', 'type'];
}
