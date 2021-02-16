<?php

namespace GetCandy\Api\Core\Categories\Drafting;

use DB;
use GetCandy\Api\Core\Drafting\Actions\DraftAssets;
use GetCandy\Api\Core\Drafting\Actions\DraftChannels;
use GetCandy\Api\Core\Drafting\Actions\DraftCustomerGroups;
use GetCandy\Api\Core\Drafting\Actions\DraftRoutes;
use GetCandy\Api\Core\Drafting\Actions\PublishAssets;
use GetCandy\Api\Core\Drafting\Actions\PublishChannels;
use GetCandy\Api\Core\Drafting\Actions\PublishCustomerGroups;
use GetCandy\Api\Core\Drafting\Actions\PublishRoutes;
use GetCandy\Api\Core\Drafting\BaseDrafter;
use GetCandy\Api\Core\Events\ModelPublishedEvent;
use GetCandy\Api\Core\Search\Actions\IndexObjects;
use Illuminate\Database\Eloquent\Model;
use NeonDigital\Drafting\Interfaces\DrafterInterface;
use Versioning;

class CategoryDrafter extends BaseDrafter implements DrafterInterface
{
    public function publish(Model $draft)
    {
        return DB::transaction(function () use ($draft) {
            // Publish this category and remove the parent.
            $parent = $draft->publishedParent;

            // Create a version of the parent before we publish these changes
            Versioning::with('categories')->create($parent, null, $parent->id);

            $parent->attribute_data = $draft->attribute_data;
            $parent->sort = $draft->sort;
            $parent->layout_id = $draft->layout_id;
            $parent->save();

            $this->callActions(array_merge([
                PublishRoutes::class,
                PublishChannels::class,
                PublishCustomerGroups::class,
                PublishAssets::class,
            ], $this->extendedPublishActions), [
                'draft' => $draft,
                'parent' => $parent,
            ]);

            $parent->products()->sync([]);

            $parent->products()->sync($draft->products()->groupBy('product_id')->pluck('product_id'), true);

            /**
             * Go through and assign any products that are for the draft to the parent.
             */
            $draft->products()->update([
                'category_id' => $parent->id,
            ]);

            // Fire off an event so plugins can update anything their side too.
            event(new ModelPublishedEvent($draft, $parent));

            // // Update all products...
            $parent = $parent->refresh();

            // Update all products...
            if ($parent->products->count()) {
                IndexObjects::dispatch([
                    'documents' => $parent->products,
                ]);
            }

            $draft->forceDelete();

            return $parent;
        });
    }

    public function create(Model $parent)
    {
        return DB::transaction(function () use ($parent) {
            $parent = $parent->load([
                'children',
                'products',
                'channels',
                'routes',
                'assets',
                'customerGroups',
                'attributes',
            ]);

            $draft = $parent->replicate();
            $draft->drafted_at = now();
            $draft->draft_parent_id = $parent->id;
            $draft->_lft = $parent->_lft;
            $draft->_rgt = $parent->_rgt;
            $draft->parent_id = $parent->parent_id;
            $draft->save();

            $this->callActions(array_merge([
                DraftRoutes::class,
                DraftAssets::class,
                DraftChannels::class,
                DraftCustomerGroups::class,
            ], $this->extendedDraftActions), [
                'draft' => $draft,
                'parent' => $parent,
            ]);

            $draft->products()->sync($parent->products()->groupBy('product_id')->pluck('product_id'));

            $parent->attributes->each(function ($model) use ($draft) {
                $draft->attributes()->attach($model);
            });

            return $draft->refresh()->load([
                'children',
                'products',
                'channels',
                'routes',
                'assets',
                'customerGroups',
                'attributes',
            ]);
        });
    }

    public function firstOrCreate(Model $parent)
    {
        return $parent->draft ?: $this->create($parent);
    }
}
