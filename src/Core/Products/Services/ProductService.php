<?php

namespace GetCandy\Api\Core\Products\Services;

use GetCandy;
use GetCandy\Api\Core\Attributes\Events\AttributableSavedEvent;
use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Customers\Actions\FetchCustomerGroups;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Languages\Actions\FetchDefaultLanguage;
use GetCandy\Api\Core\Products\Actions\FetchProductFamily;
use GetCandy\Api\Core\Products\Events\ProductCreatedEvent;
use GetCandy\Api\Core\Products\Interfaces\ProductInterface;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Products\Models\ProductRecommendation;
use GetCandy\Api\Core\Routes\Actions\CreateRoute;
use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Scopes\CustomerGroupScope;
use GetCandy\Api\Core\Search\Events\IndexableSavedEvent;
use Illuminate\Database\Eloquent\Model;

class ProductService extends BaseService
{
    /**
     * @var \GetCandy\Api\Core\Products\Models\Product
     */
    protected $model;

    /**
     * The product factory instance.
     *
     * @var \GetCandy\Api\Core\Products\Interfaces\ProductInterface
     */
    protected $factory;

    public function __construct(ProductInterface $factory)
    {
        $this->model = new Product();
        $this->factory = $factory;
    }

    /**
     * Returns model by a given hashed id.
     *
     * @param  string  $id
     * @param  bool  $withDrafted
     * @return \GetCandy\Api\Core\Products\Models\Product
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getByHashedId($id, $withDrafted = false)
    {
        $id = $this->model->decodeId($id);
        $product = $this->model;

        if ($withDrafted) {
            $product = $product->withDrafted();
        }

        return $this->factory->init($product->findOrFail($id))->get();
    }

    public function findById($id, array $includes = [], $draft = false)
    {
        $query = Product::with(array_merge($includes, ['draft']));

        if ($draft) {
            $query->withDrafted()->withoutGlobalScopes();
        }

        $product = $query->find($id);

        return $product;
    }

    public function findBySku($sku, array $includes = [], $draft = false)
    {
        $query = Product::with(array_merge($includes, ['draft']))
            ->whereHas('variants', function ($q) use ($sku) {
                $q->whereSku($sku);
            });

        if ($draft) {
            $query->withDrafted()->withoutGlobalScopes();
        }

        return $query->first();
    }

    /**
     * Updates a resource from the given data.
     *
     * @param  string  $hashedId
     * @param  array  $data
     * @return \GetCandy\Api\Core\Products\Models\Product
     *
     * @throws \Exception
     * @throws \GetCandy\Exceptions\InvalidLanguageException
     */
    public function update($hashedId, array $data)
    {
        $product = $this->getByHashedId($hashedId, true);

        if (! $product) {
            abort(404);
        }

        if (! empty($data['attribute_data'])) {
            $product->attribute_data = $data['attribute_data'];
        }

        if (! empty($data['family_id'])) {
            $family = FetchProductFamily::run([
                'encoded_id' => $data['family_id'],
            ]);
            $family->products()->save($product);
        }

        if (! empty($data['layout_id'])) {
            $layout = GetCandy::layouts()->getByHashedId($data['layout_id']);
            $product->layout_id = $layout->id;
        }

        $product->save();

        // event(new AttributableSavedEvent($product));

        // event(new IndexableSavedEvent($product));

        return $product;
    }

    /**
     * Update a products layout.
     *
     * @param  string  $productId
     * @param  string  $layoutId
     * @return \GetCandy\Api\Core\Products\Models\Product
     */
    public function updateLayout($productId, $layoutId)
    {
        $layout = GetCandy::layouts()->getByHashedId($layoutId);
        $product = $this->getByHashedId($productId);

        $product->layout->associate($layout);
        $product->save();

        return $product;
    }

    /**
     * Creates a resource from the given data.
     *
     * @param  array  $data
     * @return \GetCandy\Api\Core\Products\Models\Product
     *
     * @throws \GetCandy\Exceptions\InvalidLanguageException
     */
    public function create(array $data)
    {
        $product = new Product;

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

        // $layout = GetCandy::layouts()->getByHashedId($data['layout_id']);
        // $product->layout()->associate($layout);

        if (! empty($data['family_id'])) {
            $family = FetchProductFamily::run([
                'encoded_id' => $data['family_id'],
            ]);
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

        $language = FetchDefaultLanguage::run();
        CreateRoute::run([
            'element_type' => Product::class,
            'element_id' => $product->encoded_id,
            'language_id' => $language->encoded_id,
            'slug' => $data['url'],
            'default' => true,
            'redirect' => false,
        ]);

        $sku = $data['sku'];
        $i = 1;
        while (GetCandy::productVariants()->existsBySku($sku)) {
            $sku = $sku.$i;
            $i++;
        }

        $variant = $this->createVariant($product, [
            'options' => [],
            'stock' => $data['stock'] ?? 0,
            'incoming' => $data['incoming'] ?? 0,
            'sku' => trim($sku),
            'price' => $data['price'],
            'pricing' => $this->getPriceMapping($data['price']),
            'min_qty' => $data['min_qty'] ?? 1,
            'unit_qty' => $data['unit_qty'] ?? 1,
        ]);

        if (! empty($data['tax_id'])) {
            $variant->tax()->associate(
                GetCandy::taxes()->getByHashedId($data['tax_id'])
            );
            $variant->save();
        } else {
            $variant->tax()->associate(
                GetCandy::taxes()->getDefaultRecord()
            );
            $variant->save();
        }

        event(new ProductCreatedEvent($product));

        return $product;
    }

    protected function getPriceMapping($price)
    {
        $customerGroups = FetchCustomerGroups::run([
            'paginate' => false,
        ]);

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
     *
     * @param  \GetCandy\Api\Core\Products\Models\Product  $product
     * @param  array  $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createVariant(Product $product, array $data = [])
    {
        $data['attribute_data'] = $product->attribute_data;

        return $product->variants()->create($data);
    }

    /**
     * @param  string  $id
     * @return bool
     */
    public function delete($id)
    {
        $product = Product::withDrafted()->find($id);

        if ($product->isDraft()) {
            return $product->forceDelete();
        }

        return $product->delete();
    }

    /**
     * Gets paginated data for the record.
     *
     * @param  string|null  $channel
     * @param  int  $length
     * @param  int|null  $page
     * @param  array  $ids
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
     *
     * @param  string  $id
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
        ])->withDrafted()->find($id);

        foreach ($product->family->attributes as $attribute) {
            $attributes[$attribute->handle] = $attribute;
        }

        // Direct attributes override family ones
        foreach ($product->attributes as $attribute) {
            $attributes[$attribute->handle] = $attribute;
        }

        return $attributes;
    }

    public function getSearchedIds($ids = [], array $includes = [])
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

        $query = $this->model->with($includes)->whereIn('products.id', $parsedIds);

        if (count($parsedIds)) {
            $query = $query->orderByRaw("field(products.id,{$placeholders})", $parsedIds);
        }

        return $query->get();
    }

    /**
     * Gets recommended products based on an array of products.
     *
     * @param  array|\Illuminate\Database\Eloquent\Collection  $products
     * @param  int  $limit
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
     *
     * @param  \GetCandy\Api\Core\Products\Models\Product  $product
     * @return \Illuminate\Database\Eloquent\Collection
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
     *
     * @param  string  $id
     * @param  array  $data
     * @return \GetCandy\Api\Core\Products\Models\Product
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updateCollections($id, array $data)
    {
        $ids = [];

        $product = $this->getByHashedId($id);

        foreach ($data['collections'] as $attribute) {
            $ids[] = GetCandy::collections()->getDecodedId($attribute);
        }

        $product->collections()->sync($ids);

        return $product;
    }

    /**
     * Get products by a stock threshold.
     *
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
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
