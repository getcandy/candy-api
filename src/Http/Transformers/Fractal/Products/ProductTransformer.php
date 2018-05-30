<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Products;

use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Traits\IncludesAttributes;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Assets\AssetTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Routes\RouteTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Layouts\LayoutTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Channels\ChannelTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Categories\CategoryTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Collections\CollectionTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Customers\CustomerGroupTransformer;

class ProductTransformer extends BaseTransformer
{
    use IncludesAttributes;

    /**
     * @var array
     */
    protected $availableIncludes = [
        'assets',
        'associations',
        'attribute_groups',
        'categories',
        'channels',
        'collections',
        'customer_groups',
        'family',
        'layout',
        'routes',
        'variants',
        'first_variant',
    ];

    /**
     * @param \GetCandy\Api\Products\Models\Product $product
     *
     * @return array
     */
    public function transform(Product $product)
    {
        // clock()->startEvent('transforming_' . $product->id, 'Transforming Product');
        $this->applyDiscounts($product);

        $response = [
            'id' => $product->encodedId(),
            'attribute_data' => $product->attribute_data,
            'option_data' => $this->parseOptionData($product->option_data),
            'thumbnail' => $this->getThumbnail($product),
            'max_price' => $product->max_price,
            'min_price' => $product->min_price,
            'variant_count' => $product->variants->count(),
        ];

        if ($product->pivot) {
            $response['type'] = $product->pivot->type;
            $response['position'] = $product->pivot->position;
        }

        // clock()->endEvent('transforming_' . $product->id);

        return $response;
    }

    protected function applyDiscounts(Product $product)
    {
        // $discounts = app('api')->discounts()->get();
        // $sets = app('api')->discounts()->parse($discounts);

        $product->max_price = 0;
        $product->min_price = 0;
        $product->original_max_price = 0;
        $product->original_min_price = 0;

        foreach ($product->variants as $variant) {
            $variantPrice = $variant->total_price;

            $product->max_price = $variantPrice > $product->max_price ? $variantPrice : $product->max_price;
            if ($product->min_price) {
                $product->min_price = $variantPrice < $product->min_price ? $variantPrice : $product->min_price;
            } else {
                $product->min_price = $product->max_price;
            }
        }

        // $applied = \Facades\GetCandy\Api\Discounts\Factory::getApplied($sets, \Auth::user(), $product);

        // \Facades\GetCandy\Api\Discounts\Factory::apply($applied, $product);
        return $product;
    }

    protected function parseOptionData($data)
    {
        $data = $this->sortOptions($data);
        foreach ($data as $optionKey => $option) {
            $sorted = $this->sortOptions($option['options']);
            $data[$optionKey]['options'] = $sorted;
        }

        return $data;
    }

    protected function sortOptions($options)
    {
        uasort($options, function ($a, $b) {
            return $a['position'] < $b['position'] ? -1 : 1;
        });

        return $options;
    }

    /**
     * @param \GetCandy\Api\Products\Models\Product $product
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeLayout(Product $product)
    {
        if (! $product->layout) {
            return;
        }

        return $this->item($product->layout, new LayoutTransformer);
    }

    /**
     * @param \GetCandy\Api\Products\Models\Product $product
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeFamily(Product $product)
    {
        if (! $product->family) {
            return;
        }

        return $this->item($product->family, new ProductFamilyTransformer);
    }

    /**
     * @param \GetCandy\Api\Products\Models\Product $product
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeCollections(Product $product)
    {
        return $this->collection($product->collections, new CollectionTransformer);
    }

    /**
     * @param \GetCandy\Api\Products\Models\Product $product
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeAssociations(Product $product)
    {
        return $this->collection($product->associations, new ProductAssociationTransformer);
    }

    /**
     * @param \GetCandy\Api\Products\Models\Product $product
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeAssets(Product $product)
    {
        return $this->collection($product->assets()->orderBy('position', 'asc')->get(), new AssetTransformer);
    }

    /**
     * Includes any product variants.
     *
     * @param  Product $product
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeVariants(Product $product)
    {
        return $this->collection($product->variants, new ProductVariantTransformer);
    }

    /**
     * @param Product $product
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeRoutes(Product $product)
    {
        return $this->collection($product->routes, new RouteTransformer);
    }

    /**
     * @param Product $product
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeChannels(Product $product)
    {
        $channels = app('api')->channels()->getChannelsWithAvailability($product, 'products');

        return $this->collection($channels, new ChannelTransformer);
    }

    /**
     * @param Product $product
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeCustomerGroups(Product $product)
    {
        $groups = app('api')->customerGroups()->getGroupsWithAvailability($product, 'products');

        return $this->collection($groups, new CustomerGroupTransformer);
    }

    /**
     * @param Product $product
     *
     * @return \League\Fractal\Resource\Categories
     */
    public function includeCategories(Product $product)
    {
        return $this->collection($product->categories, new CategoryTransformer);
    }

    /**
     * @param Product $product
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeFirstVariant(Product $product)
    {
        return $this->item($product->variants->first(), new ProductVariantTransformer);
    }
}
