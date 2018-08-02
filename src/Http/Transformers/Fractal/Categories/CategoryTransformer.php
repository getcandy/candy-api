<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Categories;

use League\Fractal\ParamBag;
use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Attributes\Models\AttributeGroup;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Assets\AssetTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Routes\RouteTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Channels\ChannelTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Products\ProductTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Customers\CustomerGroupTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Attributes\AttributeGroupTransformer;

class CategoryTransformer extends BaseTransformer
{
    protected $attributeGroups;

    protected $availableIncludes = [
        'attribute_groups',
        'assets',
        'channels',
        'customer_groups',
        'products',
        'routes',
        'parent',
        'thumbnail',
        'descendants',
        'siblings',
    ];

    public function transform(Category $category)
    {
        $data = [
            'id' => $category->encodedId(),
            'sort' => $category->sort,
            'attribute_data' => $category->attribute_data,
            'depth' => $category->depth,
            'products_count' => $category->products->count(),
            'parent_id' => app('api')->categories()->getEncodedId($category->parent_id),
        ];

        if (! is_null($category->aggregate_selected)) {
            $data['aggregate_selected'] = $category->aggregate_selected;
        }

        return $data;
    }

    public function includeSiblings(Category $category)
    {
        return $this->collection($category->getSiblings(), $this);
    }

    public function includeDescendants(Category $category)
    {
        return $this->collection($category->descendants()->where('parent_id', '=', $category->id)->get(), $this);
    }

    public function includeParent(Category $category)
    {
        if (! $category->parent) {
            return;
        }

        return $this->item($category->parent, $this);
    }

    public function includeChildren(Category $category)
    {
        $desc = $category->descendants()->withDepth()->having('depth', '<', 3)->get();
        // dump($category);
        // dd($desc);
        return $this->collection($desc, $this);
    }

    public function includeProducts(Category $category, ParamBag $params = null)
    {
        return $this->paginateInclude('products', $category, $params, new ProductTransformer);
    }

    public function includeRoutes(Category $category)
    {
        return $this->collection($category->routes, new RouteTransformer);
    }

    /**
     * @param Category $category
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeChannels(Category $category)
    {
        $channels = app('api')->channels()->getChannelsWithAvailability($category, 'categories');

        return $this->collection($channels, new ChannelTransformer);
    }

    /**
     * @return mixed
     */
    public function getAttributeGroups()
    {
        if (! $this->attributeGroups) {
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
    public function includeAttributeGroups(Category $category)
    {
        $attributeIds = $category->attributes->pluck('id')->toArray();

        if ($category->family) {
            $attributeIds = array_merge(
                $attributeIds,
                $category->family->attributes->pluck('id')->toArray()
            );
        }

        $attributeGroups = $this->getAttributeGroups()->filter(function ($group) use ($attributeIds) {
            if ($group->attributes->whereIn('id', $attributeIds)->count()) {
                return $group;
            }
        });

        return $this->collection($attributeGroups, new AttributeGroupTransformer);
    }

    public function includeAssets(Category $category)
    {
        return $this->collection($category->assets()->orderBy('position', 'asc')->get(), new AssetTransformer);
    }

    /**
     * @param Product $product
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeCustomerGroups(Category $category)
    {
        $groups = app('api')->customerGroups()->getGroupsWithAvailability($category, 'categories');

        return $this->collection($groups, new CustomerGroupTransformer);
    }
}
