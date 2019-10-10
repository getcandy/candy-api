<?php

namespace GetCandy\Api\Core\Assets\Drivers;

use Storage;
use Image as InterventionImage;
use Symfony\Component\Finder\SplFileInfo;
use GetCandy\Api\Core\Assets\Jobs\GenerateTransforms;
use Intervention\Image\Exception\NotReadableException;
use GetCandy\Api\Core\Assets\Contracts\AssetDriverContract;

class Image extends BaseUploadDriver implements AssetDriverContract
{
    /**
     * @param array $data
     * @param       $model
     *
     * @return \GetCandy\Api\Core\Assets\Models\Asset
     */
    public function process(array $data, $model)
    {
        $source = app('api')->assetSources()->getByHandle($model->settings['asset_source']);

        $asset = $this->prepare($data, $source);

        try {
            // If it's not a jpeg or a PNG then encode it.
            if ($asset->extension == 'bmp') {
                $image = InterventionImage::make($data['file'])->encode('jpg');
                $asset->filename = str_replace($asset->extension, 'jpg', $asset->filename);
                $asset->extension = 'jpg';
            } else {
                $image = InterventionImage::make($data['file']);
            }
            $asset->width = $image->width();
            $asset->height = $image->height();
        } catch (NotReadableException $e) {
            //
        }

        $asset->primary = !$model->assets()->where('kind', '!=', 'application')->exists();

        $model->assets()->save($asset);

        if ($data['file'] instanceof SplFileInfo) {
            Storage::disk($source->disk)->put($asset->location.'/'.$asset->filename, $data['file']->getContents());
        } else {
            $data['file']->storeAs($asset->location, $asset->filename, $source->disk);
        }

        if (! empty($image)) {
            GenerateTransforms::dispatch($asset);
        }

        return $asset;
    }
}
