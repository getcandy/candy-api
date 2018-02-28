<?php

namespace GetCandy\Api\Assets\Drivers;

use GetCandy\Api\Assets\Contracts\AssetDriverContract;
use GetCandy\Api\Assets\Models\Asset;
use Illuminate\Database\Eloquent\Model;

abstract class BaseUploadDriver
{
    /**
     * @var bool
     */
    protected $upload = true;

    /**
     * @param array $data
     * @param       $source
     *
     * @return \GetCandy\Api\Assets\Models\Asset
     */
    public function prepare(array $data, $source)
    {
        $file = $data['file'];
        $mimeType = explode('/', $file->getClientMimeType());
        $extension = $file->clientExtension();

        if (!$extension) {
            $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        }

        $asset = new Asset([
            'kind' => $mimeType[0],
            'sub_kind' => ! empty($mimeType[1]) ? $mimeType[1] : null,
            'size' => $file->getSize(),
            'original_filename' => $file->getClientOriginalName(),
            'title' => $file->getClientOriginalName(),
            'filename' => $file->hashName(),
            'extension' => $extension
        ]);

        $asset->source()->associate($source);
        $path = $source->path . '/' . substr($asset->filename, 0, 2);
        $asset->location = $path;
        return $asset;
    }

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
        $data['file']->storeAs($asset->location, $asset->filename, $source->disk);
        $model->assets()->save($asset);
        return $asset;
    }
}
