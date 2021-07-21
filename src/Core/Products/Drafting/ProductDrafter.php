<?php

namespace GetCandy\Api\Core\Products\Drafting;

use DB;
use GetCandy\Api\Core\Drafting\Actions\DraftAssets;
use GetCandy\Api\Core\Drafting\Actions\DraftCategories;
use GetCandy\Api\Core\Drafting\Actions\DraftChannels;
use GetCandy\Api\Core\Drafting\Actions\DraftCustomerGroups;
use GetCandy\Api\Core\Drafting\Actions\DraftProductAssociations;
use GetCandy\Api\Core\Drafting\Actions\DraftProductVariants;
use GetCandy\Api\Core\Drafting\Actions\DraftRoutes;
use GetCandy\Api\Core\Drafting\Actions\PublishAssets;
use GetCandy\Api\Core\Drafting\Actions\PublishChannels;
use GetCandy\Api\Core\Drafting\Actions\PublishCustomerGroups;
use GetCandy\Api\Core\Drafting\Actions\PublishProductAssociations;
use GetCandy\Api\Core\Drafting\Actions\PublishProductVariants;
use GetCandy\Api\Core\Drafting\Actions\PublishRoutes;
use GetCandy\Api\Core\Drafting\BaseDrafter;
use GetCandy\Api\Core\Search\Events\IndexableSavedEvent;
use Illuminate\Database\Eloquent\Model;
use NeonDigital\Drafting\Interfaces\DrafterInterface;
use Versioning;

class ProductDrafter extends BaseDrafter implements DrafterInterface
{
    public function create(Model $parent)
    {
        return DB::transaction(function () use ($parent) {
            $parent = $parent->load([
                'variants',
                'categories',
                'routes.publishedParent',
                'routes.draft',
                'channels',
                'customerGroups',
            ]);

            $draft = $parent->replicate();
            $draft->drafted_at = now();
            $draft->draft_parent_id = $parent->id;
            $draft->save();

            $this->callActions(array_merge([
                DraftProductVariants::class,
                DraftRoutes::class,
                DraftProductAssociations::class,
                DraftAssets::class,
                DraftCategories::class,
                DraftChannels::class,
                DraftCustomerGroups::class,
            ], $this->extendedDraftActions), [
                'draft' => $draft,
                'parent' => $parent,
            ]);

            // Not sure if this is something we need to worry about now as drafting has changed.
            // Potentially deprecated in a later release...
            $parent->attributes->each(function ($model) use ($draft) {
                $draft->attributes()->attach($model);
            });

            return $draft->refresh()->load([
                'assets',
                'variants.publishedParent',
                'categories',
                'routes.publishedParent',
                'routes.draft',
                'channels',
                'customerGroups',
            ]);
        });
    }

    /**
     * Duplicate a product.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $product
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function firstOrCreate(Model $parent)
    {
        return $parent->draft ?: $this->create($parent);
    }

    public function publish(Model $draft)
    {
        return DB::transaction(function () use ($draft) {
            // Publish this product and remove the parent.
            $parent = $draft->publishedParent->load(
                'variants',
                'categories',
                'routes',
                'channels',
                'customerGroups',
            );

            // Get any current versions and assign them to this new product.

            // Create a version of the parent before we publish these changes
            Versioning::with('products')->create($parent);

            // Publish any attributes etc
            $parent->attribute_data = $draft->attribute_data;
            $parent->option_data = $draft->option_data;
            $parent->product_family_id = $draft->product_family_id;
            $parent->layout_id = $draft->layout_id;
            $parent->group_pricing = $draft->group_pricing;

            $parent->save();

            $this->callActions(array_merge([
                PublishProductVariants::class,
                PublishChannels::class,
                PublishCustomerGroups::class,
                PublishRoutes::class,
                PublishAssets::class,
                PublishProductAssociations::class,
            ], $this->extendedPublishActions), [
                'draft' => $draft,
                'parent' => $parent,
            ]);

            // Categories
            $existingCategories = $parent->categories;

            // Sync product categories to the parent.
            $parent->categories()->sync(
                $draft->categories->pluck('id')
            );
            // Collections
            $parent->collections()->sync(
                $draft->collections->pluck('id')
            );

            // Delete the draft we had.
            $draft->forceDelete();

            IndexableSavedEvent::dispatch($parent);

            return $parent;
        });
    }
}
