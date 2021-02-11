<?php

namespace GetCandy\Api\Core\Categories\Drafting;

use DB;
use GetCandy\Api\Core\Drafting\BaseDrafter;
use GetCandy\Api\Core\Events\ModelPublishedEvent;
use GetCandy\Api\Core\Search\Actions\IndexObjects;
use Illuminate\Database\Eloquent\Model;
use NeonDigital\Drafting\Interfaces\DrafterInterface;
use Versioning;

class CategoryDrafter extends BaseDrafter implements DrafterInterface
{
    public function create(Model $model)
    {
    }

    public function publish(Model $category)
    {
        // Publish this category and remove the parent.
        $parent = $category->publishedParent;

        // Create a version of the parent before we publish these changes
        Versioning::with('categories')->create($parent, null, $parent->id);

        $parent->attribute_data = $category->attribute_data;
        $parent->sort = $category->sort;
        $parent->layout_id = $category->layout_id;
        $parent->save();

        /**
         * Here we go through any routes the draft has and if they have a published
         * parent counterpart. We update it and then remove the draft route.
         *
         * If the parent doesn't exist then we reassign the new route to the published record.
         */
        foreach ($category->routes as $route) {
            if ($route->publishedParent) {
                $route->publishedParent->update($route->toArray());
                $route->forceDelete();
            } else {
                $route->update([
                    'element_id' => $parent->id
                ]);
            }
        }

        /**
         * Go through the draft channels and update our parent.
         */
        $channels = $category->channels->mapWithKeys(function ($channel) {
            return [$channel->id => [
                'published_at' => $channel->pivot->published_at
            ]];
        })->toArray();
        $parent->channels()->sync($channels);

        $customerGroups = $category->customerGroups->mapWithKeys(function ($group) {
            return [$group->id => [
                'purchasable' => $group->pivot->purchasable,
                'visible' => $group->pivot->visible,
            ]];
        })->toArray();

        $parent->customerGroups()->sync($customerGroups);

        /**
         * Go through and assign any products that are for the draft to the parent.
         */
        $category->products()->update([
            'category_id' => $parent->id
        ]);

        // Fire off an event so plugins can update anything their side too.
        event(new ModelPublishedEvent($category, $parent));

        // // Update all products...
        $parent = $parent->refresh();

        if ($parent->products->count()) {
            IndexObjects::run([
                'documents' => $parent->products,
            ]);
        }

        $category->forceDelete();
        // event(new ModelPublishedEvent($category));

        return $parent;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $category
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function firstOrCreate(Model $category)
    {
        return $category->draft ?: DB::transaction(function () use ($category) {
            $category = $category->load([
                'children',
                'products',
                'channels',
                'routes',
                'assets',
                'customerGroups',
                'attributes',
            ]);

            $newCategory = $category->replicate();
            $newCategory->drafted_at = now();
            $newCategory->draft_parent_id = $category->id;
            $newCategory->_lft = $category->_lft;
            $newCategory->_rgt = $category->_rgt;
            $newCategory->parent_id = $category->parent_id;
            $newCategory->save();

            $newCategory->products()->attach($category->products->pluck('id'));

            $category->routes->each(function ($r) use ($newCategory) {
                $new = $r->replicate();
                $new->element_id = $newCategory->id;
                $new->element_type = get_class($newCategory);
                $new->drafted_at = now();
                $new->draft_parent_id = $r->id;
                $new->save();
            });

            $category->attributes->each(function ($model) use ($newCategory) {
                $newCategory->attributes()->attach($model);
            });

            $this->processAssets($category, $newCategory);
            $this->processChannels($category, $newCategory);
            $this->processCustomerGroups($category, $newCategory);
            $newCategory->refresh();

            return $newCategory->load([
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
}
