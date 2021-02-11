<?php

namespace GetCandy\Api\Core\Products\Drafting;

use DB;
use Versioning;
use Illuminate\Database\Eloquent\Model;
use GetCandy\Api\Core\Products\Models\Product;
use NeonDigital\Drafting\Interfaces\DrafterInterface;
use GetCandy\Api\Core\Products\Actions\Drafting\UpdateAssets;
use GetCandy\Api\Core\Products\Actions\Drafting\UpdateRoutes;
use GetCandy\Api\Core\Products\Actions\Drafting\UpdateChannels;
use GetCandy\Api\Core\Products\Actions\Drafting\UpdateCustomerGroups;
use GetCandy\Api\Core\Products\Actions\Drafting\UpdateProductVariants;
use GetCandy\Api\Core\Products\Actions\Drafting\UpdateProductAssociations;

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

        // Create a version of the parent before we publish these changes
        Versioning::with('products')->create($parent, null, $parent->id);

        // Update any attributes etc
        $parent->attribute_data = $product->attribute_data;
        $parent->option_data = $product->option_data;
        $parent->product_family_id = $product->product_family_id;
        $parent->layout_id = $product->layout_id;
        $parent->group_pricing = $product->group_pricing;

        $parent->save();

        // Activate any product variants
        UpdateProductVariants::run([
            'draft' => $product,
            'product' => $parent,
        ]);

        /**
         * Go through the draft channels and update our parent.
         */
        UpdateChannels::run([
            'draft' => $product,
            'parent' => $parent,
        ]);
        UpdateCustomerGroups::run([
            'draft' => $product,
            'parent' => $parent,
        ]);

        /**
         * Here we go through any routes the draft has and if they have a published
         * parent counterpart. We update it and then remove the draft route.
         *
         * If the parent doesn't exist then we reassign the new route to the published record.
         */
        UpdateRoutes::run([
            'draft' => $product,
            'parent' => $parent,
        ]);

        /**
         * Go through any assets and sync with the parent.
         */
        UpdateAssets::run([
            'draft' => $product,
            'parent' => $parent,
        ]);

        // Product Associations
        // Delete any associations we don't have anymore...
        UpdateProductAssociations::run([
            'draft' => $product,
            'product' => $parent
        ]);

        // Categories
        $existingCategories = $parent->categories;

        // Sync product categories to the parent.
        $parent->categories()->sync(
            $product->categories->pluck('id')
        );
        // Collections
        $parent->collections()->sync(
            $product->collections->pluck('id')
        );
        // Delete the draft we had.
        $product->forceDelete();

        dd('End');
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

                // Copy customer group pricing...
                $groupPricing = $v->customerPricing->map(function ($groupPrice) {
                    return $groupPrice->only([
                        'customer_group_id',
                        'tax_id',
                        'price',
                        'compare_at_price',
                    ]);
                });

                $new->customerPricing()->createMany($groupPricing);
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
