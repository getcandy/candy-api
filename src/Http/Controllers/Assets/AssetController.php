<?php

namespace GetCandy\Api\Http\Controllers\Assets;

use Carbon\Carbon;
use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Assets\UpdateAllRequest;
use GetCandy\Api\Http\Requests\Assets\UploadRequest;
use GetCandy\Api\Http\Resources\Assets\AssetResource;
use Illuminate\Http\Request;
use Image;
use Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AssetController extends BaseController
{
    public function storeSimple(Request $request)
    {
        $file = $request->file('file');

        $directory = 'uploads/'.Carbon::now()->format('d/m');

        $path = $file->store($directory, 'public');
        $thumbnail = null;

        // You can't transform a PDF so...
        try {
            $image = Image::make(Storage::disk('public')->get($path));
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $filename = basename($path, ".{$type}");
            $image->resize(null, 300, function ($constraint) {
                $constraint->aspectRatio();
            });
            $thumbnail = "{$directory}/thumbnails/{$filename}.{$type}";
            Storage::disk('public')->put(
                $thumbnail,
                $image->stream($type, 100)->getContents()
            );
        } catch (NotReadableException $e) {
        }

        return response()->json([
            'path' => $path,
            'filename' => $file->getClientOriginalName(),
            'url'=> Storage::disk('cdn')->url($path),
            'thumbnail' => $thumbnail ?? null,
            'thumbnail_url' => ! empty($thumbnail) ? \Storage::disk('cdn')->url($thumbnail) : null,
        ]);
    }

    public function store(UploadRequest $request)
    {
        $parent = null;
        if ($request->parent_id) {
            $parent = GetCandy::{$request->parent}()->getByHashedId($request->parent_id, true);
        }

        $data = $request->all();

        if (empty($data['caption'])) {
            $data['caption'] = $parent ? $parent->attribute('name') : $request->caption;
        }

        $data = $request->all();

        if (empty($data['alt']) && $parent) {
            $data['alt'] = $parent->attribute('name');
        }

        $asset = GetCandy::assets()->upload(
            $data,
            $parent,
            $parent ? $parent->assets()->count() + 1 : 1
        );

        if (! $asset) {
            return $this->respondWithError('Unable to upload asset');
        }

        return new AssetResource($asset);
    }

    public function detach($assetId, $ownerId, Request $request)
    {
        try {
            GetCandy::assets()->detach($assetId, $ownerId, $request->type);
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }

    public function destroy($id)
    {
        try {
            GetCandy::assets()->delete($id);
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }

    public function updateAll(UpdateAllRequest $request)
    {
        $result = GetCandy::assets()->updateAll($request->assets);
        if (! $result) {
            $this->respondWithError();
        }

        return $this->respondWithComplete();
    }
}
