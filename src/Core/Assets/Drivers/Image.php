<?php

namespace GetCandy\Api\Core\Assets\Drivers;

use GetCandy;
use GetCandy\Api\Core\Assets\Contracts\AssetDriverContract;
use GetCandy\Api\Core\Assets\Jobs\GenerateTransforms;
use Image as InterventionImage;
use Intervention\Image\Exception\NotReadableException;
use Storage;
use Symfony\Component\Finder\SplFileInfo;

class Image extends BaseUploadDriver implements AssetDriverContract
{
    /**
     * @param  array  $data
     * @param  null|\Illuminate\Database\Eloquent\Model  $model
     * @return \GetCandy\Api\Core\Assets\Models\Asset
     */
    public function process(array $data, $model = null)
    {
        $assetSources = GetCandy::assetSources();

        if ($model) {
            $source = GetCandy::assetSources()->getByHandle($model->settings['asset_source']);
        } else {
            $source = $assetSources->getDefaultRecord();
        }

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

        $asset->save();

        if ($model) {
            $model->assets()->attach($asset, [
                'primary' => ! $model->assets()->images()->exists(),
                'assetable_type' => get_class($model),
                'position' => $model->assets()->count() + 1,
            ]);
        }

        if ($data['file'] instanceof SplFileInfo) {
            Storage::disk($source->disk)->put($asset->location.'/'.$asset->filename, $data['file']->getContents());
        } else {
            $data['file']->storeAs($asset->location, $asset->filename, $source->disk);
        }

        if (! empty($image)) {
            GenerateTransforms::dispatch($asset, $model ? $model->settings : null);
        }

        return $asset;
    }
}
