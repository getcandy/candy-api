<?php

namespace GetCandy\Api\Core\Assets\Drivers;

use GetCandy;
use GetCandy\Api\Core\Assets\Jobs\GenerateTransforms;
use GetCandy\Api\Core\Assets\Models\Asset;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Intervention\Image\Exception\NotReadableException;

abstract class BaseUrlDriver
{
    /**
     * @var bool
     */
    protected $upload = true;

    /**
     * @var null|string
     */
    protected $hashedName = null;

    /**
     * @var array
     */
    protected $info;

    /**
     * @var \GetCandy\Api\Core\Assets\Models\AssetSource
     */
    protected $source;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * @var array
     */
    protected $data;

    /**
     * @param  array  $data
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \GetCandy\Api\Core\Assets\Models\Asset
     */
    public function process(array $data, Model $model = null)
    {
        $assetSources = GetCandy::assetSources();

        if ($model) {
            $this->source = GetCandy::assetSources()->getByHandle($model->settings['asset_source']);
        } else {
            $this->source = $assetSources->getDefaultRecord();
        }

        $this->data = $data;

        $this->getInfo($this->data['url']);

        $asset = $this->prepare();

        if ($model) {
            if ($model->assets()->count()) {
                // Get anything that isn't an "application";
                $image = $model->assets()->where('kind', '!=', 'application')->first();
                if (! $image) {
                    $asset->primary = true;
                }
            } else {
                $asset->primary = true;
            }
            $model->assets()->attach($asset);
        }

        $asset->save();
        dispatch(new GenerateTransforms($asset));

        return $asset;
    }

    /**
     * Prepares the asset.
     *
     * @param  array  $data
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \GetCandy\Api\Core\Assets\Models\Asset
     */
    protected function prepare()
    {
        $asset = new Asset([
            'location' => $this->getUniqueId($this->data['url']),
            'title' => $this->info['title'],
            'kind' => $this->handle,
            'external' => true,
        ]);
        $asset->source()->associate($this->source);

        return $asset;
    }

    /**
     * Generates a hashed name.
     *
     * @return string
     */
    public function hashName()
    {
        return Str::random(40);
    }

    /**
     * Get the thumbnail for the video.
     *
     * @return null|\Intervention\Image\Image
     */
    public function getThumbnail()
    {
        $thumbnail = $this->getImageFromUrl($this->info['thumbnail_url']);

        return $thumbnail ?: null;
    }

    /**
     * Gets an image from a given url.
     *
     * @param  string  $url
     * @return null|Intervention\Image\Image
     */
    public function getImageFromUrl($url)
    {
        try {
            $image = app('image')->make($url);
        } catch (NotReadableException $e) {
            $image = null;
        }

        return $image;
    }

    /**
     * Get the OEM data.
     *
     * @param  array  $params
     * @return mixed
     */
    protected function getOemData($params = [])
    {
        $client = new Client();
        try {
            $response = $client->request('GET', $this->oemUrl, [
                'query' => $params,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (ClientException $e) {
            //
        }
    }
}
