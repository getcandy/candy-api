<?php

namespace GetCandy\Api\Http\Controllers\Search;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Search\StoreRequest;
use GetCandy\Api\Http\Transformers\Fractal\Search\SavedSearchTransformer;
use Illuminate\Http\Request;

class SavedSearchController extends BaseController
{
    public function store(StoreRequest $request)
    {
        $search = GetCandy::savedSearch()->store($request->all());

        return $this->respondWithItem($search, new SavedSearchTransformer);
    }

    public function getByType($type, Request $request)
    {
        $result = GetCandy::savedSearch()->getByType($type);

        return $this->respondWithCollection($result, new SavedSearchTransformer);
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
