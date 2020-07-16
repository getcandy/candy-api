<?php

namespace GetCandy\Api\Core\Products\Drafting;

use DB;
use GetCandy\Api\Core\Products\Events\ProductCreatedEvent;
use GetCandy\Api\Core\Products\Models\Product;
use Illuminate\Database\Eloquent\Model;
use NeonDigital\Drafting\Interfaces\DrafterInterface;
use Versioning;

class ProductDrafter implements DrafterInterface
{
    public function create(Model $product)
    {
        dd('Hello!');
    }

    public function publish(Model $product)
    {
        // Publish this product and remove the parent.
        $parent = $product->publishedParent;
        // Get any current versions and assign them to this new product.

        foreach ($parent->versions as $version) {
            $version->update([
                'versionable_id' => $product->id,
            ]);
        }

        // Create a version of the parent before we publish these changes
        Versioning::with('products')->create($parent, null, $product->id);

        // Move the activities onto the draft
        $parent->activities->each(function ($a) use ($product) {
            $a->update(['subject_id' => $product->id]);
        });

        // Activate any product variants
        $variantIds = $product->variants->pluck('id')->toArray();

        DB::table('product_variants')
            ->whereIn('id', $variantIds)
            ->update([
                'drafted_at' => null,
            ]);

        // Activate any routes
        $routeIds = $product->routes()->onlyDrafted()->get()->pluck('id')->toArray();

        DB::table('routes')
            ->whereIn('id', $routeIds)
            ->update([
                'drafted_at' => null,
            ]);

        // Delete routes
        // $parent->routes()->delete();

        $parent->forceDelete();

        $product->drafted_at = null;
        $product->save();

        event(new ProductCreatedEvent($product));

        return $product;
    }

    /**
     * Duplicate a product.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $product
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function firstOrCreate(Model $product)
    {
        return $product->draft ?: DB::transaction(function () use ($product) {
            $product = $product->load([
                'variants',
                'categories',
                'routes',
                'channels',
                'customerGroups',
            ]);
            $newProduct = $product->replicate();
            $newProduct->drafted_at = now();
            $newProduct->draft_parent_id = $product->id;
            $newProduct->save();

            $product->variants->each(function ($v) use ($newProduct) {
                $new = $v->replicate();
                $new->product_id = $newProduct->id;
                $new->drafted_at = now();
                $new->draft_parent_id = $v->id;
                $new->save();
            });

            $product->routes->each(function ($r) use ($newProduct) {
                $new = $r->replicate();
                $new->element_id = $newProduct->id;
                $new->element_type = get_class($newProduct);
                $new->drafted_at = now();
                $new->draft_parent_id = $r->id;
                $new->save();
            });

            $product->attributes->each(function ($model) use ($newProduct) {
                $newProduct->attributes()->attach($model);
            });

            $product->associations->each(function ($model) use ($newProduct) {
                $assoc = $model->replicate();
                $assoc->product_id = $newProduct->id;
                $assoc->save();
            });
            $newProduct->refresh();

            $this->processAssets($product, $newProduct);
            $this->processCategories($product, $newProduct);
            $this->processChannels($product, $newProduct);
            $this->processCustomerGroups($product, $newProduct);
            $newProduct->refresh();

            return $newProduct->load([
                'variants',
                'channels',
                'routes',
                'customerGroups',
            ]);
        });
    }

    /**
     * Process the assets for a duplicated product.
     *
     * @param  \GetCandy\Api\Core\Products\Models\Product  $oldProduct
     * @param  \GetCandy\Api\Core\Products\Models\Product  $newProduct
     * @return void
     */
    protected function processAssets($oldProduct, &$newProduct)
    {
        foreach ($oldProduct->assets as $asset) {
            $newProduct->assets()->attach(
                $asset->id,
                [
                    'primary' => $asset->pivot->primary,
                    'assetable_type' => $asset->pivot->assetable_type,
                ]
            );
        }
    }

    /**
     * Process the duplicated product categories.
     *
     * @param  \GetCandy\Api\Core\Products\Models\Product  $oldProduct
     * @param  \GetCandy\Api\Core\Products\Models\Product  $newProduct
     * @return void
     */
    protected function processCategories($oldProduct, &$newProduct)
    {
        $currentCategories = $oldProduct->categories;
        foreach ($currentCategories as $category) {
            $newProduct->categories()->attach($category);
        }
    }

    /**
     * Process the customer groups for the duplicated product.
     *
     * @param  \GetCandy\Api\Core\Products\Models\Product  $oldProduct
     * @param  \GetCandy\Api\Core\Products\Models\Product  $newProduct
     * @return void
     */
    protected function processCustomerGroups($oldProduct, &$newProduct)
    {
        // Need to associate all the channels the current product has
        // but make sure they are not active to start with.
        $groups = $oldProduct->customerGroups;

        $newGroups = collect();

        foreach ($groups as $group) {
            // \DB::table()
            $newGroups->put($group->id, [
                'visible' => $group->pivot->visible,
                'purchasable' => $group->pivot->purchasable,
            ]);
        }

        $newProduct->customerGroups()->sync($newGroups->toArray());
    }

    /**
     * Process channels for a duplicated product.
     *
     * @param  \GetCandy\Api\Core\Products\Models\Product  $oldProduct
     * @param  \GetCandy\Api\Core\Products\Models\Product  $newProduct
     * @return void
     */
    protected function processChannels($oldProduct, &$newProduct)
    {
        // Need to associate all the channels the current product has
        // but make sure they are not active to start with.
        $channels = $oldProduct->channels;

        $newChannels = collect();

        foreach ($channels as $channel) {
            $newChannels->put($channel->id, [
                'published_at' => now(),
            ]);
        }

        $newProduct->channels()->sync($newChannels->toArray());
    }
}
