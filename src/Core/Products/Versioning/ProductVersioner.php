<?php

namespace GetCandy\Api\Core\Products\Versioning;

use Auth;
use GetCandy\Api\Core\Assets\Models\Asset;
use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Products\Models\ProductVariant;
use GetCandy\Api\Core\Routes\Models\Route;
use Illuminate\Database\Eloquent\Model;
use NeonDigital\Versioning\Interfaces\VersionerInterface;
use NeonDigital\Versioning\Version;
use NeonDigital\Versioning\Versioners\AbstractVersioner;
use Versioning;

class ProductVersioner extends AbstractVersioner implements VersionerInterface
{
    public function create(Model $product, $relationId = null, $originatorId = null)
    {
        $userId = Auth::user() ? Auth::user()->id : null;

        $attributes = $product->getAttributes();

        if (is_string($attributes['attribute_data'])) {
            $attributes['attribute_data'] = json_decode($attributes['attribute_data'], true);
        }
        // Base model
        $version = new Version;
        $version->user_id = $userId;
        $version->versionable_type = get_class($product);
        $version->versionable_id = $originatorId ?: $product->id;
        $version->model_data = json_encode($attributes);
        $version->save();

        // Channels
        foreach ($product->channels as $channel) {
            $data = array_merge($channel->getAttributes(), [
                'pivot' => $channel->pivot->getAttributes(),
            ]);
            $this->createFromObject($channel, $version->id, $data);
        }

        // Variants
        foreach ($product->variants as $variant) {
            Versioning::with('product_variants')->create($variant, $version->id);
        }

        // Categories
        foreach ($product->categories as $category) {
            $data = array_merge($category->getAttributes(), [
                'pivot' => $category->pivot->getAttributes(),
            ]);
            $this->createFromObject($category, $version->id, $data);
        }

        foreach ($product->customerGroups as $group) {
            $data = array_merge($group->getAttributes(), [
                'pivot' => $group->pivot->getAttributes(),
            ]);
            $this->createFromObject($group, $version->id, $data);
        }

        // Routes
        foreach ($product->routes()->get() as $route) {
            $this->createFromObject($route, $version->id);
        }

        // Assets
        foreach ($product->assets as $asset) {
            Versioning::with('assets')->create($asset, $version->id);
        }

        return $version;
    }

    public function restore($version)
    {
        $current = $version->versionable;

        // Do we already have a draft??
        $draft = $current->draft;
        // This is the new draft so...remove it.
        if ($draft) {
            $draft->forceDelete();
        }
        // Okay so, hydrate this draft...
        $data = $version->model_data;
        unset($data['id']);
        $product = new Product;
        $product->forceFill($data);

        // Make it a draft
        $product->drafted_at = now();
        $product->draft_parent_id = $version->versionable_id;
        $product->save();
        $product->attribute_data = $data['attribute_data'];
        $product->save();

        foreach ($version->relations as $relation) {
            $type = $relation->versionable_type;
            $data = $relation->model_data;

            switch ($type) {
                case ProductVariant::class:
                    Versioning::with('product_variants')->restore($relation, $product);
                    break;
                case Channel::class:
                    $product->channels()->sync([
                        $data['id'] => [
                            'published_at' => $data['pivot']['published_at'] ?? now(),
                        ],
                    ]);
                    break;
                case CustomerGroup::class:
                    $product->customerGroups()->sync([
                        $data['id'] => [
                            'purchasable' => $data['pivot']['purchasable'] ?? 1,
                            'visible' => $data['pivot']['visible'] ?? 1,
                        ],
                    ]);
                    break;
                case Category::class:
                    $product->categories()->sync($data['id']);
                    break;
                case Route::class:
                    $route = new Route;
                    $route->fill($relation->model_data);
                    $route->element_type = get_class($product);
                    $route->element_id = $product->id;
                    $route->drafted_at = now();
                    $route->draft_parent_id = $relation->versionable_id;
                    $route->save();

                    break;
                case Asset::class:
                    $data = $relation->model_data;

                    if (! Asset::find($data['asset_id'])) {
                        break;
                    }

                    $product->assets()->attach($data['asset_id'], [
                        'position' => $data['position'] ?? 1,
                        'primary' => $data['primary'] ?? true,
                    ]);

                    break;
                default:
                    break;
            }
        }

        return $product;
    }
}
