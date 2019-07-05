<?php

namespace GetCandy\Api\Core\Assets\Drivers;

use Storage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use GetCandy\Api\Core\Assets\Models\Asset;
use GetCandy\Api\Core\Assets\Jobs\GenerateTransforms;

class ExternalImage extends BaseUrlDriver
{
    /**
     * @var InterventionImage
     */
    protected $manager;

    /**
     * @var string
     */
    protected $handle = 'external';

    public function __construct()
    {
        $this->manager = app('image');
    }

    /**
     * Process the external image.
     *
     * @param array $data
     * @param Model $model
     *
     * @return Asset
     */
    public function process(array $data, Model $model)
    {
        $this->source = app('api')->assetSources()->getByHandle($model->settings['asset_source']);

        // if (!$this->info) {
        $this->getInfo($data['url']);
        // }

        $this->model = $model;
        $this->data = $data;
        $asset = $this->prepare();

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

        $image = $this->getImageFromUrl($data['url']);

        $storage = Storage::disk($this->source->disk);

        if (! $image) {
            return false;
        }

        $storage->put(
            $asset->location.'/'.$asset->filename,
            $image->stream()->getContents()
        );

        // Now it's actually saved, we can get the size...
        $filesize = $storage->size($asset->location.'/'.$asset->filename);

        $asset->update(['size' => $filesize]);

        dispatch(new GenerateTransforms($asset));

        return $asset;
    }

    /**
     * @param array $data
     * @param       $source
     *
     * @return \GetCandy\Api\Core\Assets\Models\Asset
     */
    public function prepare()
    {
        // $file = $data['file'];
        $mimeType = explode('/', $this->info['kind']);
        $extension = pathinfo($this->info['thumbnail_url'], PATHINFO_EXTENSION);

        $asset = new Asset([
            'kind' => $mimeType[0],
            'sub_kind' => ! empty($mimeType[1]) ? $mimeType[1] : null,
            'original_filename' => $this->info['title'],
            'title' => $this->info['title'],
            'width' => $this->info['width'],
            'height' => $this->info['height'],
            'filename' => $this->hashName().'.'.$extension,
            'extension' => $extension,
        ]);

        $asset->source()->associate($this->source);

        $path = $this->source->path.'/'.Carbon::now()->format('Y/m/d');
        $asset->location = $path;

        return $asset;
    }

    /**
     * Get the asset info.
     *
     * @param string $url
     *
     * @return array
     */
    public function getInfo($url)
    {
        $image = $this->getImageFromUrl($url);

        if (! $image) {
            return;
        }
        // if (!$this->info) {
        return $this->info = [
                'thumbnail_url' => $url,
                'width' => $image->width(),
                'height' => $image->height(),
                'kind' => $image->mime(),
                'title' => basename($url),
            ];
        // }/
        // return $this->info;
    }
}
