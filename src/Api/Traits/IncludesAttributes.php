<?php

namespace GetCandy\Api\Traits;

use GetCandy\Api\Attributes\Models\AttributeGroup;
use GetCandy\Api\Http\Transformers\Fractal\Attributes\AttributeGroupTransformer;
use Illuminate\Database\Eloquent\Model;

trait IncludesAttributes
{
    /**
     * @var
     */
    protected $attributeGroups;

    /**
     * @return mixed
     */
    public function getAttributeGroups()
    {
        if (!$this->attributeGroups) {
            $this->attributeGroups = AttributeGroup::select('id', 'name', 'handle', 'position')
                ->orderBy('position', 'asc')->with(['attributes'])->get();
        }

        return $this->attributeGroups;
    }

    /**
     * @param \GetCandy\Api\Products\Models\Product $product
     *
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

        return $this->collection($attributeGroups, new AttributeGroupTransformer());
    }
}
