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

        // Get any current versions and assign them to this new category.

        foreach ($parent->versions as $version) {
            $version->update([
                'versionable_id' => $category->id,
            ]);
        }

        // Create a version of the parent before we publish these changes
        Versioning::with('categories')->create($parent, null, $category->id);

        // Move the activities onto the draft
        if ($parent->activities) {
            $parent->activities->each(function ($a) use ($category) {
                $a->update(['subject_id' => $category->id]);
            });
        }

        // Activate any routes
        $routeIds = $category->routes()->onlyDrafted()->get()->pluck('id')->toArray();

        DB::table('routes')
            ->whereIn('id', $routeIds)
            ->update([
                'drafted_at' => null,
            ]);

        // Delete routes
        $parent->routes()->delete();
        $parent->forceDelete();

        $category->drafted_at = null;
        $category->save();

        // Update all products...
        IndexObjects::run([
            'documents' => $category->products,
        ]);

        event(new ModelPublishedEvent($category));

        return $category;
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
