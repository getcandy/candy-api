<?php

namespace GetCandy\Api\Core\Versioning\Actions;

use GetCandy\Api\Core\Scaffold\AbstractAction;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;

class VersionAssets extends AbstractAction
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('manage-versions');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'version' => 'required',
            'model' => 'required',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function handle()
    {
        foreach ($this->model->assets as $asset) {
            // Okay so due to assets being quite fluid, we need to take a copy of the asset
            // and store in a dedicated versions folder. That way if it gets deleted we can retrieve
            // it and use it. If we can't copy the file for whatever reason. Then hopefully it'll be there still...
            $source = $asset->source;
            $target = "versions/{$asset->location}";
            $disk = $source->disk;

            // Don't copy transforms here, we'll do that if we have to restore it...
            try {
                Storage::disk($source->disk)->copy("{$asset->location}/{$asset->filename}", "{$target}/{$asset->filename}");
            } catch (FileNotFoundException $e) {
            } catch (FileExistsException $e) {
                // Hey, it exists, so don't worry.
            }

            (new CreateVersion)->actingAs($this->user())->run([
                'model' => $asset,
                'model_data' => array_merge([
                    'version_location' => $target,
                    'disk' => $disk,
                ], $asset->getAttributes(), $asset->pivot->toArray()),
                'relation' => $this->version,
            ]);
        }

        return $this->version;
    }
}
