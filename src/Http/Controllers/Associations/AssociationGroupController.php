<?php

namespace GetCandy\Api\Http\Controllers\Associations;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Resources\Associations\AssociationGroupCollection;
use Illuminate\Http\Request;

class AssociationGroupController extends BaseController
{
    /**
     * Returns a listing of association groups.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \GetCandy\Api\Http\Resources\Associations\AssociationGroupCollection
     */
    public function index(Request $request)
    {
        $groups = GetCandy::associationGroups()->getPaginatedData();

        return new AssociationGroupCollection($groups);
    }
}
