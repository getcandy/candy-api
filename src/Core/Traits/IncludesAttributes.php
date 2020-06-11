<?php

namespace GetCandy\Api\Core\Traits;

use GetCandy\Api\Core\Attributes\Models\AttributeGroup;
use GetCandy\Api\Http\Transformers\Fractal\Attributes\AttributeGroupTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Attributes\AttributeTransformer;
use Illuminate\Database\Eloquent\Model;

trait IncludesAttributes
{
    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $attributeGroups;

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAttributeGroups()
    {
        if (! $this->attributeGroups) {
            $this->attributeGroups = AttributeGroup::select('id', 'name', 'handle', 'position')
                ->orderBy('position', 'asc')->with(['attributes'])->get();
        }

        return $this->attributeGroups;
    }

    public function includeAttributes(Model $model)
    {
        return $this->collection($model->attributes, new AttributeTransformer);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \League\Fractal\Resource\Collection
     */
    public function includeAttributeGroups(Model $model)
    {
        $attributeIds = $model->attributes->pluck('id')->toArray();

        if ($model->family) {
            $attributeIds = array_merge(
                $attributeIds,
                $model->family->attributes->pluck('id')->toArray()
            );
        }

        $attributeGroups = $this->getAttributeGroups()->filter(function ($group) use ($attributeIds) {
            if ($group->attributes->whereIn('id', $attributeIds)->count()) {
                return $group;
            }
        });

        return $this->collection($attributeGroups, new AttributeGroupTransformer);
    }
}
