<?php

namespace GetCandy\Api\Core\Assets\Drivers;

use Carbon\Carbon;
use Symfony\Component\Finder\SplFileInfo;
use GetCandy\Api\Core\Assets\Models\Asset;

abstract class BaseUploadDriver
{
    /**
     * @var bool
     */
    protected $upload = true;

    /**
     * Get the video unique id.
     *
     * @param string $url
     *
     * @return string
     */
    public function getUniqueId($url)
    {
        return $url;
    }

    /**
     * @param array $data
     * @param       $source
     *
     * @return \GetCandy\Api\Core\Assets\Models\Asset
     */
    public function prepare(array $data, $source)
    {
        $file = $data['file'];

        if ($file instanceof SplFileInfo) {
            $mimeType = 'image';
            $extension = $file->getExtension();
            $original_filename = $file->getFilename();
            $filename = $data['filename'].'.'.$extension ?? $original_filename;
            $subType = null;
        } else {
            $mtFragments = explode('/', $file->getClientMimeType());
            $mimeType = $mtFragments[0];
            $subType = $mtFragments[1] ?? null;

            $extension = $file->clientExtension();
            $original_filename = $file->getClientOriginalName();
            $filename = $file->hashName();

            if (! $extension) {
                $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
            }
        }

        // If a one for one asset already exists, return it.
        if ($existing = Asset::where('filename', '=', $filename)->first()) {
            return $existing;
        }

        $asset = new Asset([
            'kind' => $mimeType,
            'sub_kind' => $subType,
            'size' => $file->getSize(),
            'original_filename' => $original_filename,
            'title' => $data['alt'] ?? $filename,
            'filename' => $filename,
            'extension' => $extension,
        ]);

        $asset->source()->associate($source);

        $path = $source->path.'/'.Carbon::now()->format('Y/m/d');

        $asset->location = $path;

        return $asset;
    }

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
        $data['file']->storeAs($asset->location, $asset->filename, $source->disk);
        $model->assets()->save($asset);

        return $asset;
    }
}
