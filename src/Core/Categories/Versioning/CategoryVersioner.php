<?php

namespace GetCandy\Api\Core\Categories\Versioning;

use Auth;
use Drafting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use NeonDigital\Versioning\Interfaces\VersionerInterface;
use NeonDigital\Versioning\Version;
use NeonDigital\Versioning\Versioners\AbstractVersioner;

class CategoryVersioner extends AbstractVersioner implements VersionerInterface
{
    public function create(Model $category, $relationId = null, $originatorId = null)
    {
        $userId = Auth::user() ? Auth::user()->id : null;

        $attributes = $category->getAttributes();

        if (is_string($attributes['attribute_data'])) {
            $attributes['attribute_data'] = json_decode($attributes['attribute_data'], true);
        }

        // Base model
        $version = new Version;
        $version->user_id = $userId;
        $version->versionable_type = get_class($category);
        $version->versionable_id = $originatorId ?: $category->id;
        $version->model_data = json_encode($attributes);
        $version->save();

        return $version;
    }

    public function restore($version)
    {
        $current = $version->versionable;

        // This is the new draft so...remove it.
        $category = Drafting::with('categories')->firstOrCreate($current);

        // Okay so, hydrate this draft...
        $data = $version->model_data;
        $category->forceFill(Arr::except($data, ['id', 'drafted_at', 'draft_parent_id']));

        $category->attribute_data = $data['attribute_data'];
        $category->save();

        return $category;
    }
}
