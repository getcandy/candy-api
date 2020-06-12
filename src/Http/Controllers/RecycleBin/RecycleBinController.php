<?php

namespace GetCandy\Api\Http\Controllers\RecycleBin;

use GetCandy\Api\Core\RecycleBin\Interfaces\RecycleBinServiceInterface;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Resources\RecycleBin\RecycleBinCollection;
use GetCandy\Api\Http\Resources\RecycleBin\RecycleBinResource;
use Hashids;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class RecycleBinController extends BaseController
{
    public function __construct(RecycleBinServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * Handles request to get all recycle bin items.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \GetCandy\Api\Http\Resources\RecycleBin\RecycleBinCollection
     */
    public function index(Request $request)
    {
        $items = $this->service->getItems(
            $request->page ?: 1,
            $request->per_page ?: 25,
            $request->terms
        );

        return new RecycleBinCollection($items);
    }

    /**
     * Handles request to get all recycle bin items.
     *
     * @param  string  $id
     * @param  \Illuminate\Http\Request  $request
     * @return array|\GetCandy\Api\Http\Resources\RecycleBin\RecycleBinResource
     */
    public function show($id, Request $request)
    {
        $realId = Hashids::connection('recycle_bin')->decode($id);
        if (! $realId = $realId[0] ?? null) {
            return $this->errorNotFound();
        }

        try {
            $items = $this->service->findById($realId, $request->includes);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new RecycleBinResource($items);
    }

    public function restore($id, Request $request)
    {
        $realId = Hashids::connection('recycle_bin')->decode($id);

        if (! $realId = $realId[0] ?? null) {
            return $this->errorNotFound();
        }

        $this->service->restore($realId);
    }

    public function destroy($id, Request $request)
    {
        $realId = Hashids::connection('recycle_bin')->decode($id);

        if (! $realId = $realId[0] ?? null) {
            return $this->errorNotFound();
        }

        $this->service->forceDelete($realId);
    }
}
