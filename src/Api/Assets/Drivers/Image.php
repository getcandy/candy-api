<?php

namespace GetCandy\Api\Assets\Drivers;

use Image as InterventionImage;
use GetCandy\Api\Assets\Jobs\GenerateTransforms;
use GetCandy\Api\Assets\Contracts\AssetDriverContract;

class Image extends BaseUploadDriver implements AssetDriverContract
{
    /**
     * @param array $data
     * @param       $model
     *
     * @return \GetCandy\Api\Assets\Models\Asset
     */
    public function process(array $data, $model)
    {
        $source = app('api')->assetSources()->getByHandle($model->settings['asset_source']);
        $asset = $this->prepare($data, $source);

        try {
            $image = InterventionImage::make($data['file']);
            $asset->width = $image->width();
            $asset->height = $image->height();
        } catch (NotReadableException $e) {
            // Fall through
        }

        if ($model->assets()->count()) {
            // Get anything that isn't an "application";
            $image = $model->assets()->where('kind', '!=', 'application')->first();
            if (! $image) {
                $asset->primary = true;
            }
        } else {
            $asset->primary = true;
        }

        $model->assets()->save($asset);
        $data['file']->storeAs($asset->location, $asset->filename, $source->disk);

        if (! empty($image)) {
            dispatch(new GenerateTransforms($asset));
        }

        return $asset;
    }
}
