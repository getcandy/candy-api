<?php

namespace GetCandy\Api\Core\Assets\Actions;

use GetCandy\Api\Core\Assets\Models\Asset;
use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;

class ReorderAssets extends AbstractAction
{
    use ReturnsJsonResponses;

    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('manage-drafts');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'assets' => 'required|array',
            'assets.*.id' => 'required',
            'assets.*.position' => 'required',
            'assets.*.primary' => 'nullable',
            'assetable_type' => 'required',
            'assetable_id' => 'required',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function handle()
    {
        switch ($this->assetable_type) {
            case 'category':
                $realId = (new Category)->decodeId($this->assetable_id);
                $model = Category::withDrafted()->find($realId);
                break;
            default:
                $realId = (new Product)->decodeId($this->assetable_id);
                $model = Product::withDrafted()->find($realId);
            break;
        }

        $assets = collect($this->assets)->mapWithKeys(function ($asset) {
            $assetId = (new Asset)->decodeId($asset['id']);
            unset($asset['id']);

            return [
                $assetId => $asset,
            ];
        });

        $model->assets()->sync($assets->toArray());
    }

    public function response($result, $request)
    {
        return $this->respondWithNoContent();
    }
}
