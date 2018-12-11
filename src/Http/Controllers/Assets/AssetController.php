<?php

namespace GetCandy\Api\Http\Controllers\Assets;

use GetCandy\Exceptions\InvalidServiceException;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Assets\UploadRequest;
use GetCandy\Api\Http\Requests\Assets\UpdateAllRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use GetCandy\Api\Http\Transformers\Fractal\Assets\AssetTransformer;

class AssetController extends BaseController
{
    public function store(UploadRequest $request)
    {
        try {
            $parent = app('api')->{$request->parent}()->getByHashedId($request->parent_id);
        } catch (InvalidServiceException $e) {
            return $this->errorWrongArgs($e->getMessage());
        }

        $asset = app('api')->assets()->upload(
            $request->all(),
            $parent,
            $parent->assets()->count() + 1
        );

        if (! $asset) {
            return $this->respondWithError('Unable to upload asset');
        }

        return $this->respondWithItem($asset, new AssetTransformer);
    }

    public function destroy($id)
    {
        try {
            $result = app('api')->assets()->delete($id);
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }

    public function updateAll(UpdateAllRequest $request)
    {
        $result = app('api')->assets()->updateAll($request->assets);
        if (! $result) {
            $this->respondWithError();
        }

        return $this->respondWithComplete();
    }
}
