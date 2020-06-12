<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Collections;

use GetCandy\Api\Core\Collections\Models\Collection;
use GetCandy\Api\Core\Traits\IncludesAttributes;
use GetCandy\Api\Http\Transformers\Fractal\Assets\AssetTransformer;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Channels\ChannelTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Customers\CustomerGroupTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Products\ProductTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Routes\RouteTransformer;
use League\Fractal\ParamBag;

class CollectionTransformer extends BaseTransformer
{
    use IncludesAttributes;

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = [
        'routes',
    ];

    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = [
        'assets',
        'attribute_groups',
        'products',
        'channels',
        'customer_groups',
    ];

    /**
     * Decorates the collection object for viewing.
     *
     * @param  \GetCandy\Api\Core\Collections\Models\Collection  $collection
     * @return array
     */
    public function transform(Collection $collection)
    {
        return [
            'id' => $collection->encodedId(),
            'attribute_data' => $collection->attribute_data,
            'thumbnail' => $this->getThumbnail($collection),
        ];
    }

    /**
     * Includes the products for the collection.
     *
     * @param  \GetCandy\Api\Core\Collections\Models\Collection  $collection
     * @param  null|\League\Fractal\ParamBag  $params
     * @return \League\Fractal\Resource\Collection
     */
    public function includeProducts(Collection $collection, ParamBag $params = null)
    {
        return $this->paginateInclude('products', $collection, $params, new ProductTransformer);
    }

    public function includeAssets(Collection $collection)
    {
        return $this->collection($collection->assets()->orderBy('position', 'asc')->get(), new AssetTransformer);
    }

    /**
     * @param  \GetCandy\Api\Core\Collections\Models\Collection  $collection
     * @return \League\Fractal\Resource\Collection
     */
    public function includeChannels(Collection $collection)
    {
        $channels = app('api')->channels()->getChannelsWithAvailability($collection, 'collections');

        return $this->collection($channels, new ChannelTransformer);
    }

    /**
     * @param  \GetCandy\Api\Core\Collections\Models\Collection  $collection
     * @return \League\Fractal\Resource\Collection
     */
    public function includeCustomerGroups(Collection $collection)
    {
        $groups = app('api')->customerGroups()->getGroupsWithAvailability($collection, 'collections');

        return $this->collection($groups, new CustomerGroupTransformer);
    }

    /**
     * @param  \GetCandy\Api\Core\Collections\Models\Collection  $collection
     * @return \League\Fractal\Resource\Collection
     */
    public function includeRoutes(Collection $collection)
    {
        return $this->collection($collection->routes, new RouteTransformer);
    }
}
