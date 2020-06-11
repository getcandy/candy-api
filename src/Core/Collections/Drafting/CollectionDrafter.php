<?php

namespace GetCandy\Api\Core\Collections\Drafting;

use DB;
use GetCandy\Api\Core\Drafting\BaseDrafter;
use Illuminate\Database\Eloquent\Model;
use NeonDigital\Drafting\Interfaces\DrafterInterface;
use Versioning;

class CollectionDrafter extends BaseDrafter implements DrafterInterface
{
    public function create(Model $model)
    {
    }

    public function publish(Model $collection)
    {
        // Publish this category and remove the parent.
        $parent = $collection->publishedParent;

        // Get any current versions and assign them to this new category.

        foreach ($parent->versions as $version) {
            $version->update([
                'versionable_id' => $collection->id,
            ]);
        }

        // Create a version of the parent before we publish these changes
        Versioning::with('collections')->create($parent, null, $collection->id);

        // Move the activities onto the draft
        if ($parent->activities) {
            $parent->activities->each(function ($a) use ($collection) {
                $a->update(['subject_id' => $collection->id]);
            });
        }

        // Activate any routes
        $routeIds = $collection->routes()->onlyDrafted()->get()->pluck('id')->toArray();

        DB::table('routes')
            ->whereIn('id', $routeIds)
            ->update([
                'drafted_at' => null,
            ]);

        // Delete routes
        $parent->routes()->delete();
        $parent->forceDelete();

        $collection->drafted_at = null;
        $collection->save();

        return $collection;
    }

    /**
     * Duplicate a product.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $collection
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function firstOrCreate(Model $collection)
    {
        return $collection->draft ?: DB::transaction(function () use ($collection) {
            $collection = $collection->load([
                'products',
                'channels',
                'routes',
                'assets',
                'customerGroups',
                'attributes',
            ]);

            $newCollection = $collection->replicate();
            $newCollection->drafted_at = now();
            $newCollection->draft_parent_id = $collection->id;
            $newCollection->save();

            $newCollection->products()->attach($collection->products->pluck('id'));

            $collection->routes->each(function ($r) use ($newCollection) {
                $new = $r->replicate();
                $new->element_id = $newCollection->id;
                $new->element_type = get_class($newCollection);
                $new->drafted_at = now();
                $new->draft_parent_id = $r->id;
                $new->save();
            });

            $collection->attributes->each(function ($model) use ($newCollection) {
                $newCollection->attributes()->attach($model);
            });

            $this->processAssets($collection, $newCollection);
            $this->processChannels($collection, $newCollection);
            $this->processCustomerGroups($collection, $newCollection);
            $newCollection->refresh();

            return $newCollection->load([
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
