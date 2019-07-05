<?php

namespace GetCandy\Api\Http\Controllers\Assets;

use Image;
use Storage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use GetCandy\Exceptions\InvalidServiceException;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Assets\UploadRequest;
use GetCandy\Api\Http\Requests\Assets\UpdateAllRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use GetCandy\Api\Http\Transformers\Fractal\Assets\AssetTransformer;

class AssetController extends BaseController
{
    public function storeSimple(Request $request)
    {
        $file = $request->file('file');

        $directory = 'public/uploads/'.Carbon::now()->format('d/m');

        $path = $file->store($directory);

        // You can't transform a PDF so...
        try {
            $image = Image::make(Storage::get($path));
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $filename = basename($path, ".{$type}");
            $image->resize(500, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $image->crop(500, 300, 0, 0);
            $thumbnail = "{$directory}/thumbnails/{$filename}.{$type}";
            Storage::put(
                $thumbnail,
                $image->stream($type, 100)->getContents()
            );
        } catch (NotReadableException $e) {
        }

        return response()->json([
            'path' => $path,
            'url'=> \Storage::url($path),
            'thumbnail' => $thumbnail ?? null,
            'thumbnail_url' => ! empty($thumbnail) ? \Storage::url($thumbnail) : null,
        ]);
    }

    public function store(UploadRequest $request)
    {
        try {
            $parent = app('api')->{$request->parent}()->getByHashedId($request->parent_id);
        } catch (InvalidServiceException $e) {
            return $this->errorWrongArgs($e->getMessage());
        }

        $data = $request->all();

        if (empty($data['alt'])) {
            $data['alt'] = $parent->attribute('name');
        }

        $asset = app('api')->assets()->upload(
            $data,
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
