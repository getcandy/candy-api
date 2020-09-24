<?php

namespace GetCandy\Api\Http\Controllers\Search;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Search\StoreRequest;
use GetCandy\Api\Http\Resources\Search\SavedSearchCollection;
use GetCandy\Api\Http\Resources\Search\SavedSearchResource;
use Illuminate\Http\Request;

class SavedSearchController extends BaseController
{
    public function store(StoreRequest $request)
    {
        return new SavedSearchResource(
            GetCandy::savedSearch()->store($request->all())
        );
    }

    public function getByType($type, Request $request)
    {
        return new SavedSearchCollection(
            GetCandy::savedSearch()->getByType($type)
        );
    }

    public function destroy($id)
    {
        try {
            GetCandy::savedSearch()->delete($id);
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }
    }
}
