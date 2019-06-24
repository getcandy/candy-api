<?php

namespace GetCandy\Api\Core\Products\Services;

use Illuminate\Database\Eloquent\Model;
use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Scopes\CustomerGroupScope;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Search\Events\IndexableSavedEvent;
use GetCandy\Api\Core\Products\Events\ProductCreatedEvent;
use GetCandy\Api\Core\Products\Interfaces\ProductInterface;
use GetCandy\Api\Core\Products\Models\ProductRecommendation;
use GetCandy\Api\Core\Attributes\Events\AttributableSavedEvent;

class ProductService extends BaseService
{
    protected $model;

    /**
     * The product factory instance.
     *
     * @var ProductInterface
     */
    protected $factory;

    public function __construct(ProductInterface $factory)
    {
        $this->model = new Product();
        $this->factory = $factory;
    }

    /**
     * Returns model by a given hashed id.
     * @param  string $id
     * @throws  Illuminate\Database\Eloquent\ModelNotFoundException
     * @return Product
     */
    public function getByHashedId($id)
    {
        $id = $this->model->decodeId($id);
        $product = $this->model->findOrFail($id);

        return $this->factory->init($product)->get();
    }

    /**
     * Updates a resource from the given data.
     *
     * @param  string $hashedId
     * @param  array  $data
     *
     * @throws \Symfony\Component\HttpKernel\Exception
     * @throws \GetCandy\Exceptions\InvalidLanguageException
     *
     * @return Product
     */
    public function update($hashedId, array $data)
    {
        $product = $this->getByHashedId($hashedId);

        if (! $product) {
            abort(404);
        }

        $product->attribute_data = $data['attribute_data'];

        if (! empty($data['family_id'])) {
            $family = app('api')->productFamilies()->getByHashedId($data['family_id']);
            $family->products()->save($product);
        } else {
            $product->save();
        }

        event(new AttributableSavedEvent($product));

        event(new IndexableSavedEvent($product));

        return $product;
    }

    /**
     * Update a products layout.
     *
     * @param string $productId
     * @param string $layoutId
     * @return Product
     */
    public function updateLayout($productId, $layoutId)
    {
        $layout = app('api')->layouts()->getByHashedId($layoutId);
        $product = $this->getByHashedId($productId);

        $product->layout->associate($layout);
        $product->save();

        return $product;
    }

    /**
     * Creates a resource from the given data.
     *
     * @throws \GetCandy\Exceptions\InvalidLanguageException
     *
     * @return Product
     */
    public function create(array $data)
    {
        $product = $this->model;

        $data['description'] = ! empty($data['description']) ? $data['description'] : '';
        $product->attribute_data = $data;

        if (! empty($data['historical_id'])) {
            $product->id = $data['historical_id'];
        }

        if (! empty($data['created_at'])) {
            $product->created_at = $data['created_at'];
        }

        $product->option_data = [];

        if (! empty($data['option_data'])) {
            $product->option_data = $data['option_data'];
        }

        // $layout = app('api')->layouts()->getByHashedId($data['layout_id']);
        // $product->layout()->associate($layout);

        if (! empty($data['family_id'])) {
            $family = app('api')->productFamilies()->getByHashedId($data['family_id']);
            if (! $family) {
                abort(422);
            }
            $family->products()->save($product);
        } else {
            $product->save();
        }

        if (! empty($data['customer_groups'])) {
            $groupData = $this->mapCustomerGroupData($data['customer_groups']['data']);
            $product->customerGroups()->sync($groupData);
        } else {
            $product->customerGroups()->sync(CustomerGroup::select('id')->get(), [
                'visible' => false,
                'purchasable' => false,
            ]);
        }

        if (! empty($data['channels']['data'])) {
            $product->channels()->sync(
                $this->getChannelMapping($data['channels']['data'])
            );
        } else {
            $product->channels()->sync(Channel::select('id')->get()->mapWithKeys(function ($c) {
                return [$c->id => [
                    'published_at' => null,
                ]];
            })->toArray());
        }

        $urls = $this->getUniqueUrl($data['url']);
        $product->routes()->createMany($urls);

        $sku = $data['sku'];
        $i = 1;
        while (app('api')->productVariants()->existsBySku($sku)) {
            $sku = $sku.$i;
            $i++;
        }

        $variant = $this->createVariant($product, [
            'options' => [],
            'stock' => $data['stock'] ?? 0,
            'incoming' => $data['incoming'] ?? 0,
            'sku' => $sku,
            'price' => $data['price'],
            'pricing' => $this->getPriceMapping($data['price']),
            'min_qty' => $data['min_qty'] ?? 1,
            'unit_qty' => $data['unit_qty'] ?? 1,
        ]);

        if (! empty($data['tax_id'])) {
            $variant->tax()->associate(
                app('api')->taxes()->getByHashedId($data['tax_id'])
            );
            $variant->save();
        } else {
            $variant->tax()->associate(
                app('api')->taxes()->getDefaultRecord()
            );
            $variant->save();
        }

        event(new ProductCreatedEvent($product));

        return $product;
    }

    protected function getPriceMapping($price)
    {
        $customerGroups = app('api')->customerGroups()->all();

        return $customerGroups->map(function ($group) use ($price) {
            return [
                $group->handle => [
                    'price' => $price,
                    'compare_at' => 0,
                    'tax' => 0,
                ],
            ];
        })->toArray();
    }

    /**
     * Creates a product variant.
     * @param  Product $product
     * @param  array   $data
     * @return Model
     */
    public function createVariant(Product $product, array $data = [])
    {
        $data['attribute_data'] = $product->attribute_data;

        return $product->variants()->create($data);
    }

    /**
     * @param $hashedId
     * @return mixed
     */
    public function delete($hashedId)
    {
        return $this->getByHashedId($hashedId)->delete();
    }

    /**
     * Gets paginated data for the record.
     * @param  int $length How many results per page
     * @param  int  $page   The page to start
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPaginatedData($channel = null, $length = 50, $page = null, $ids = [])
    {
        $results = $this->model->channel($channel);

        if (! empty($ids)) {
            $realIds = $this->getDecodedIds($ids);
            $results->whereIn('id', $realIds);
        }

        return $results->paginate($length, ['*'], 'page', $page);
    }

    /**
     * Gets the attributes from a given products id.
     * @param  string $id
     * @return array
     */
    public function getAttributes($id)
    {
        $id = $this->getDecodedId($id);
        $attributes = [];

        if (! $id) {
            return [];
        }

        $product = $this->model->with([
            'attributes',
            'family',
            'family.attributes',
        ])->find($id);

        foreach ($product->family->attributes as $attribute) {
            $attributes[$attribute->handle] = $attribute;
        }

        // Direct attributes override family ones
        foreach ($product->attributes as $attribute) {
            $attributes[$attribute->handle] = $attribute;
        }

        return $attributes;
    }

    public function getSearchedIds($ids = [], $user = null)
    {
        $parsedIds = [];
        foreach ($ids as $hash) {
            $id = $this->model->decodeId($hash);
            if (! $id) {
                $parsedIds[] = $hash;
            } else {
                $parsedIds[] = $id;
            }
        }

        $placeholders = implode(',', array_fill(0, count($parsedIds), '?')); // string for the query

        $query = $this->model->with([
            'routes',
            'firstVariant',
            'assets.transforms',
            'variants.product',
            'variants.tiers',
            'variants.tiers.group',
            'variants.customerPricing',
            'primaryAsset',
            'primaryAsset.transforms.transform',
            'primaryAsset.transforms.asset',
            'primaryAsset.transforms.asset.source',
            'primaryAsset.tags',
            'primaryAsset.source',
        ])->whereIn('id', $parsedIds);

        if (count($parsedIds)) {
            $query = $query->orderByRaw("field(id,{$placeholders})", $parsedIds);
        }

        return $this->factory->collection($query->get());
    }

    /**
     * Gets recommended products based on an array of products.
     *
     * @param array|\Illuminate\Database\Eloquent\Collection $products
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecommendations($products = [], $limit = 6)
    {
        return ProductRecommendation::whereIn('product_id', $products)
            ->with(
                'product.routes',
                'product.categories.assets.transforms',
                'product.variants.tiers',
                'product.assets.transforms',
                'product.firstVariant'
            )
            ->whereHas('product')
            ->select(
                'related_product_id',
                \DB::RAW('SUM(count) as count')
            )
            ->groupBy('related_product_id')
            ->orderBy('count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Gets the attributes from a given products id.
     * @param  string $id
     * @return array
     */
    public function getCategories(Product $product)
    {
        $product = $this->model
            ->with(['categories', 'routes'])
            ->find($product->id);

        return $product->categories;
    }

    /**
     * Updates the collections for a product.
     * @param  string  $id
     * @param  array  $data
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @return Model
     */
    public function updateCollections($id, array $data)
    {
        $ids = [];

        $product = $this->getByHashedId($id);

        foreach ($data['collections'] as $attribute) {
            $ids[] = app('api')->collections()->getDecodedId($attribute);
        }

        $product->collections()->sync($ids);

        return $product;
    }

    /**
     * Get products by a stock threshold.
     *
     * @param int $limit
     *
     * @return Collection
     */
    public function getByStockThreshold($limit = 15)
    {
        return $this->model
            ->with('variants')
            ->withoutGlobalScope(CustomerGroupScope::class)
            ->with(['variants' => function ($q) use ($limit) {
                return $q->where('stock', '<=', $limit);
            }])->whereHas('variants', function ($q2) use ($limit) {
                return $q2->where('stock', '<=', $limit);
            })->get();
    }
}
