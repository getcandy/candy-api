<?php

namespace GetCandy\Api\Http\Controllers\Associations;

use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Transformers\Fractal\Associations\AssociationGroupTransformer;
use Illuminate\Http\Request;

class AssociationGroupController extends BaseController
{
    /**
     * Returns a listing of association groups.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function index(Request $request)
    {
        $groups = GetCandy::associationGroups()->getPaginatedData();

        return $this->respondWithCollection($groups, new AssociationGroupTransformer);
    }
}
