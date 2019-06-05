<?php

namespace GetCandy\Api\Core\Assets\Services;

use Image;
use Storage;
use Carbon\Carbon;
use GetCandy\Api\Core\Assets\Models\Asset;
use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Assets\Models\Transform;
use GetCandy\Api\Core\Assets\Models\AssetTransform;
use Intervention\Image\Exception\NotReadableException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class AssetTransformService extends BaseService
{
    public function __construct()
    {
        $this->model = new Transform;
    }

    /**
     * @param $handle
     *
     * @return mixed
     */
    public function getByHandle($handle)
    {
        return $this->model->where('handle', '=', $handle)->first();
    }

    /**
     * @param array $handles
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getByHandles(array $handles)
    {
        return $this->model->whereIn('handle', $handles)->get();
    }

    /**
     * @param $transformer
     * @param $asset
     *
     * @return bool
     */
    protected function process($transformer, $asset)
    {
        // First, we need to get the actual file.
        $source = $asset->source;

        if ($asset->external) {
            $path = $source->path . '/' . Carbon::now()->format('Y/m/d');
        } else {
            $path = $asset->location;
        }

        $image = $this->getImage($asset);

        if (! $image) {
            return false;
        }

        $width = $transformer->width;
        $height = $transformer->height;

        // Lets sort out the width and height
        switch ($transformer->mode) {
            case 'fit':
                $background = Image::canvas($width, $height);
                $image->resize($width, $height, function ($c) {
                    $c->aspectRatio();
                    $c->upsize();
                });
                $image = $background->insert($image, 'center');
                break;
            case 'fit-crop':
                $image->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $image->crop($width, $height);
                break;
            case 'stretch':
                dd('Do the stretch');
                break;
            default:
                $image->crop($width, $height);
        }

        // Determine where to put this puppy...
        $thumbPath = $path.'/'.str_plural($transformer->handle);

        $assetTransform = new AssetTransform;
        $assetTransform->asset()->associate($asset);
        $assetTransform->transform()->associate($transformer);

        $assetTransform->location = $thumbPath;
        $assetTransform->filename = $transformer->handle.'_'.($asset->external ? $asset->location.'.jpg' : $asset->filename);
        $assetTransform->file_exists = true;

        $assetTransform->save();

        Storage::disk($source->disk)->put(
            $assetTransform->location.'/'.$assetTransform->filename,
            $image->stream($transformer->format, $transformer->quality)->getContents()
        );
    }

    /**
     * @param                                   $ref
     * @param \GetCandy\Api\Core\Assets\Models\Asset $asset
     */
    public function transform($ref, Asset $asset)
    {
        if (is_array($ref)) {
            $transformers = $this->getByHandles($ref);
        } else {
            if ($ref instanceof $this->model) {
                $transformer = $ref;
            } else {
                $transformer = $this->getByHandle($ref);
            }
            $transformers = collect($transformer);
        }
        foreach ($transformers as $transformer) {
            $this->process($transformer, $asset);
        }
    }

    /**
     * Get the image.
     *
     * @param [type] $asset
     * @return void
     */
    protected function getImage($asset)
    {
        if ($asset->external) {
            return $asset->uploader()->getThumbnail($asset->location);
        }

        try {
            $file = Storage::disk($asset->source->disk)->get($asset->location.'/'.$asset->filename);
        } catch (FileNotFoundException $e) {
            return false;
        }

        // You can't transform a PDF so...
        try {
            $image = Image::make($file);
        } catch (NotReadableException $e) {
            return false;
        }

        return $image;
    }
}
