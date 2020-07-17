<?php

namespace GetCandy\Api\Http\Controllers\Collections;

use DB;
use Drafting;
use GetCandy;
use GetCandy\Api\Core\Collections\Criteria\CollectionCriteria;
use GetCandy\Api\Core\Collections\Models\Collection;
use GetCandy\Api\Core\Collections\Services\CollectionService;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Collections\CreateRequest;
use GetCandy\Api\Http\Requests\Collections\DeleteRequest;
use GetCandy\Api\Http\Requests\Collections\UpdateRequest;
use GetCandy\Api\Http\Resources\Collections\CollectionCollection;
use GetCandy\Api\Http\Resources\Collections\CollectionResource;
use Hashids;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CollectionController extends BaseController
{
    /**
     * @var \GetCandy\Api\Core\Collections\Services\CollectionService
     */
    protected $service;

    public function __construct(CollectionService $service)
    {
        $this->service = $service;
    }

    /**
     * Returns a listing of collections.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \GetCandy\Api\Core\Collections\Criteria\CollectionCriteria  $criteria
     * @return \GetCandy\Api\Http\Resources\Collections\CollectionCollection
     */
    public function index(Request $request, CollectionCriteria $criteria)
    {
        $collection = $criteria->include($request->include)->limit(
            $request->per_page
        )->get();

        return new CollectionCollection($collection);
    }

    /**
     * Handles the request to show a collection based on it's hashed ID.
     *
     * @param  string  $id
     * @param  \Illuminate\Http\Request  $request
     * @param  \GetCandy\Api\Core\Collections\Criteria\CollectionCriteria  $criteria
     * @return array|\GetCandy\Api\Http\Resources\Collections\CollectionResource
     */
    public function show($id, Request $request, CollectionCriteria $criteria)
    {
        $id = (new Collection)->decodeId($id);

        $includes = $request->include ?: [];

        if ($includes && is_string($includes)) {
            $includes = $this->parseIncludes($includes);
        }

        if (! $id) {
            return $this->errorNotFound();
        }

        $collection = $this->service->findById($id, $includes, $request->draft);

        if (! $collection) {
            return $this->errorNotFound();
        }

        return new CollectionResource($collection);
    }

    public function publishDraft($id, Request $request)
    {
        $id = Hashids::connection('main')->decode($id);
        if (empty($id[0])) {
            return $this->errorNotFound();
        }
        $collection = $this->service->findById($id[0], [], true);

        DB::transaction(function () use ($collection) {
            Drafting::with('categories')->publish($collection);
        });

        $includes = $request->includes ? explode(',', $request->include) : [];

        return new CollectionResource($collection->load($includes));
    }

    /**
     * Handles the request to create a new collection.
     *
     * @param  \GetCandy\Api\Http\Requests\Collections\CreateRequest  $request
     * @return \GetCandy\Api\Http\Resources\Collections\CollectionResource
     */
    public function store(CreateRequest $request)
    {
        $result = GetCandy::collections()->create($request->all());

        return new CollectionResource($result);
    }

    /**
     * Handles the request to update a collection.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Collections\UpdateRequest  $request
     * @return array|\GetCandy\Api\Http\Resources\Collections\CollectionResource
     */
    public function update($id, UpdateRequest $request)
    {
        try {
            $result = GetCandy::collections()->update($id, $request->all());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return new CollectionResource($result);
    }

    public function createDraft($id, Request $request)
    {
        $collection = $this->service->getByHashedId($id, true);

        if (! $collection) {
            return $this->errorNotFound();
        }

        $draft = Drafting::with('collections')->firstOrCreate($collection);

        return new CollectionResource($draft);
    }

    /**
     * Handles the request to delete a collection.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Collections\DeleteRequest  $request
     * @return array|\Illuminate\Http\Response
     */
    public function destroy($id, DeleteRequest $request)
    {
        try {
            GetCandy::collections()->delete($id, true);
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }
}
