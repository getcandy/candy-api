<?php

namespace GetCandy\Api\Products\Services;

use GetCandy\Api\Attributes\Events\AttributableSavedEvent;
use GetCandy\Api\Products\Events\ProductCreatedEvent;
use GetCandy\Api\Products\Models\Product;
use GetCandy\Api\Scaffold\BaseService;
use GetCandy\Api\Search\Events\IndexableSavedEvent;
use Illuminate\Database\Eloquent\Model;

class ProductService extends BaseService
{
    protected $model;

    public function __construct()
    {
        $this->model = new Product();
    }

    /**
     * Updates a resource from the given data.
     *
     * @param string $hashedId
     * @param array  $data
     *
     * @throws \Symfony\Component\HttpKernel\Exception
     * @throws \GetCandy\Exceptions\InvalidLanguageException
     *
     * @return Product
     */
    public function update($hashedId, array $data)
    {
        $product = $this->getByHashedId($hashedId);

        if (!$product) {
            abort(404);
        }

        $product->attribute_data = $data['attributes'];

        if (!empty($data['family_id'])) {
            $family = app('api')->productFamilies()->getByHashedId($data['family_id']);
            $family->products()->save($product);
        } else {
            $product->save();
        }

        event(new AttributableSavedEvent($product));

        if (!empty($data['channels']['data'])) {
            $product->channels()->sync(
                $this->getChannelMapping($data['channels']['data'])
            );
        }
        if (!empty($data['customer_groups'])) {
            $groupData = $this->mapCustomerGroupData($data['customer_groups']['data']);
            $product->customerGroups()->sync($groupData);
        }

        event(new IndexableSavedEvent($product));

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

        $data['description'] = !empty($data['description']) ? $data['description'] : '';
        $product->attribute_data = $data;

        if (!empty($data['historical_id'])) {
            $product->id = $data['historical_id'];
        }

        if (!empty($data['created_at'])) {
            $product->created_at = $data['created_at'];
        }

        $product->option_data = [];

        if (!empty($data['option_data'])) {
            $product->option_data = $data['option_data'];
        }

        // $layout = app('api')->layouts()->getByHashedId($data['layout_id']);
        // $product->layout()->associate($layout);

        if (!empty($data['family_id'])) {
            $family = app('api')->productFamilies()->getByHashedId($data['family_id']);
            if (!$family) {
                abort(422);
            }
            $family->products()->save($product);
        } else {
            $product->save();
        }

        event(new AttributableSavedEvent($product));

        if (!empty($data['customer_groups'])) {
            $groupData = $this->mapCustomerGroupData($data['customer_groups']['data']);
            $product->customerGroups()->sync($groupData);
        }

        if (!empty($data['channels']['data'])) {
            $product->channels()->sync(
                $this->getChannelMapping($data['channels']['data'])
            );
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
            'stock'   => $data['stock'],
            'sku'     => $sku,
            'price'   => $data['price'],
            'pricing' => $this->getPriceMapping($data['price']),
        ]);

        if (!empty($data['tax_id'])) {
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
                    'price'      => $price,
                    'compare_at' => 0,
                    'tax'        => 0,
                ],
            ];
        })->toArray();
    }

    /**
     * Creates a product variant.
     *
     * @param Product $product
     * @param array   $data
     *
     * @return Model
     */
    public function createVariant(Product $product, array $data = [])
    {
        $data['attribute_data'] = $product->attribute_data;

        return $product->variants()->create($data);
    }

    /**
     * @param $hashedId
     *
     * @return mixed
     */
    public function delete($hashedId)
    {
        return $this->getByHashedId($hashedId)->delete();
    }

    /**
     * Gets paginated data for the record.
     *
     * @param int $length How many results per page
     * @param int $page   The page to start
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPaginatedData($channel = null, $length = 50, $page = null, $ids = [])
    {
        $results = $this->model->channel($channel);

        if (!empty($ids)) {
            $realIds = $this->getDecodedIds($ids);
            $results->whereIn('id', $realIds);
        }

        return $results->paginate($length, ['*'], 'page', $page);
    }

    /**
     * Gets the attributes from a given products id.
     *
     * @param string $id
     *
     * @return array
     */
    public function getAttributes($id)
    {
        $id = $this->getDecodedId($id);
        $attributes = [];

        if (!$id) {
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

    public function getSearchedIds($ids = [])
    {
        $parsedIds = [];
        foreach ($ids as $hash) {
            $parsedIds[] = $this->model->decodeId($hash);
        }

        $placeholders = implode(',', array_fill(0, count($parsedIds), '?')); // string for the query

        $query = $this->model->whereIn('id', $parsedIds);

        $groups = \GetCandy::getGroups();
        $user = \Auth::user();

        $ids = [];

        foreach ($groups as $group) {
            $ids[] = $group->id;
        }

        $pricing = null;

        // If the user is an admin, fall through
        if (!$user || ($user && !$user->hasRole('admin'))) {
            $query->with(['primaryAsset', 'primaryAsset.transforms', 'primaryAsset.source', 'variants' => function ($q1) use ($ids) {
                $q1->with(['customerPricing' => function ($q2) use ($ids) {
                    $q2->whereIn('customer_group_id', $ids)
                        ->orderBy('price', 'asc')
                        ->first();
                }]);
            }, 'firstVariant.image']);
        }

        if (count($parsedIds)) {
            $query = $query->orderByRaw("field(id,{$placeholders})", $parsedIds);
        }

        clock()->startEvent('p_ids', 'Get searched Product ids');
        $results = $query->get();
        clock()->endEvent('p_ids');

        return $results;
    }

    /**
     * Gets the attributes from a given products id.
     *
     * @param string $id
     *
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
     *
     * @param string $id
     * @param array  $data
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
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
}
