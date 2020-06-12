<?php

namespace GetCandy\Api\Core\Layouts\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class Layout extends BaseModel
{
    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
    protected $hashids = 'main';
}
