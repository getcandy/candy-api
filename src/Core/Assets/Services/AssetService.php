<?php

namespace GetCandy\Api\Core\Assets\Services;

use GetCandy;
use GetCandy\Api\Core\Assets\Jobs\CleanUpAssetFiles;
use GetCandy\Api\Core\Assets\Models\Asset;
use GetCandy\Api\Core\Scaffold\BaseService;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Finder\SplFileInfo;

class AssetService extends BaseService
{
    public function __construct()
    {
        $this->model = new Asset;
    }

    /**
     * Gets the driver for the upload.
     *
     * @param  string  $mimeType
     * @return mixed
     */
    public function getDriver($mimeType)
    {
        $kind = explode('/', $mimeType);

        return app("{$kind[0]}.driver");
    }

    /**
     * Uploads an asset.
     *
     * @param  array  $data
     * @param  null|\Illuminate\Database\Eloquent\Model  $model
     * @param  int  $position
     * @return \GetCandy\Api\Core\Assets\Models\Asset
     */
    public function upload($data, Model $model = null, $position = 0)
    {
        if (! empty($data['file'])) {
            if ($data['file'] instanceof SplFileInfo) {
                $mimeType = 'image';
            } else {
                $mimeType = $data['file']->getClientMimeType();
            }
        } else {
            $mimeType = $data['mime_type'];
        }

        $driver = $this->getDriver($mimeType);

        $asset = $driver->process(
            $data,
            $model
        );

        if (! $asset) {
            return false;
        }

        return $asset;
    }

    /**
     * Update all the assets.
     *
     * @param  array  $assets
     * @return bool
     */
    public function updateAll($assets)
    {
        foreach ($assets as $asset) {
            $model = $this->update($asset['id'], $asset);

            if (isset($asset['tags'])) {
                $tagIds = GetCandy::tags()->getSyncableIds($asset['tags']);
                $model->tags()->sync($tagIds);
            }
        }

        return true;
    }

    /**
     * Update an asset.
     *
     * @param  string  $id
     * @param  array  $data
     * @return \GetCandy\Api\Core\Assets\Models\Asset
     */
    public function update($id, array $data)
    {
        $asset = $this->getByHashedId($id);
        $asset->fill($data);
        $asset->save();

        return $asset;
    }

    /**
     * Get some assets.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $assetable
     * @param  array  $params
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAssets(Model $assetable, $params = [])
    {
        $assets = $assetable->assets();
        if (! empty($params['type'])) {
            if ($params['type'] == 'image') {
                $assets = $assets->where('kind', '=', 'image');
            } else {
                $assets = $assets->where('kind', '!=', 'image');
            }
        }

        return $assets->get();
    }

    /**
     * Detach an asset.
     *
     * @param  string  $assetId
     * @param  mixed  $ownerId
     * @param  mixed  $ownerType
     * @return bool
     */
    public function detach($assetId, $ownerId, $ownerType)
    {
        $ownerId = (new $ownerType)->decodeId($ownerId);

        $ownerModel = (new $ownerType)->withoutGlobalScopes()->find($ownerId);

        $assetId = (new Asset)->decodeId($assetId);
        $ownerModel->assets()->detach($assetId);

        return true;
    }

    /**
     * Delete an asset.
     *
     * @param  string  $id
     * @return bool
     */
    public function delete($id)
    {
        $asset = $this->getByHashedId($id);
        dispatch(new CleanUpAssetFiles($asset));
        $asset->delete();

        return true;
    }
}
