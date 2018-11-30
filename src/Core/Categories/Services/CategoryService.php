<?php

namespace GetCandy\Api\Core\Categories\Services;

use GetCandy\Api\Core\Routes\Models\Route;
use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Search\SearchContract;
use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Search\Events\IndexableSavedEvent;
use GetCandy\Api\Core\Attributes\Events\AttributableSavedEvent;

class CategoryService extends BaseService
{
    /**
     * @var AttributeGroup
     */
    protected $model;
    protected $route;

    public function __construct()
    {
        $this->model = new Category();
        $this->route = new Route();
    }

    /**
     * Returns model by a given hashed id.
     * @param  string $id
     * @throws  Illuminate\Database\Eloquent\ModelNotFoundException
     * @return Illuminate\Database\Eloquent\Model
     */
    public function getByHashedId($id)
    {
        $id = $this->model->decodeId($id);

        return $this->model->withDepth()->withoutGlobalScopes()->findOrFail($id);
    }

    public function getNestedList()
    {
        $categories = $this->model->withDepth()->defaultOrder()->get()->toTree();

        return $categories;
    }

    public function getByParentID($encodedParentID)
    {
        $parentID = $this->model->decodeId($encodedParentID);

        $categories = $this->model->where('parent_id', $parentID)->defaultOrder()->get();

        return $categories;
    }

    public function create(array $data)
    {
        // Create Category
        $category = $this->model;

        $category->attribute_data = $data;

        $category->save();

        if (! empty($data['customer_groups'])) {
            $groupData = $this->mapCustomerGroupData($data['customer_groups']['data']);
            $category->customerGroups()->sync($groupData);
        }

        if (! empty($data['channels']['data'])) {
            $category->channels()->sync(
                $this->getChannelMapping($data['channels']['data'])
            );
        }

        $urls = $this->getUniqueUrl($data['url']);

        $category->routes()->createMany($urls);

        // If a parent id exists then add the category to the parent
        if (! empty($data['parent']['id'])) {
            $parentNode = $this->getByHashedId($data['parent']['id']);
            $parentNode->prependNode($category);
        }

        event(new AttributableSavedEvent($category));
        event(new IndexableSavedEvent($category));

        return $category;
    }

    public function update($hashedId, array $data)
    {
        $model = $this->getByHashedId($hashedId);
        $model->attribute_data = $data['attributes'];

        if (! empty($data['customer_groups'])) {
            $groupData = $this->mapCustomerGroupData($data['customer_groups']['data']);
            $model->customerGroups()->sync($groupData);
        }

        if (! empty($data['channels']['data'])) {
            $model->channels()->sync(
                $this->getChannelMapping($data['channels']['data'])
            );
        }

        $model->save();

        event(new AttributableSavedEvent($model));
        event(new IndexableSavedEvent($model));

        return $model;
    }

    public function updateProducts($id, array $data)
    {
        $category = $this->getByHashedId($id);
        $category->sort = $data['sort_type'];
        $category->save();

        $existingProducts = $category->products;

        $ids = [];

        foreach ($data['products'] as $item) {
            $ids[app('api')->products()->getDecodedId($item['id'])] = ['position' => $item['position']];
        }

        $category->products()->sync($ids);

        if ($existingProducts->count()) {
            app(SearchContract::class)->indexer()->updateDocuments(
                $existingProducts,
                'categories'
            );
        }

        app(SearchContract::class)->indexer()->updateDocuments(
            $category->products()->get(),
            'categories'
        );

        return $category;
    }

    /**
     * Update a category layout.
     *
     * @param string $categoryId
     * @param string $layoutId
     * @return Product
     */
    public function updateLayout($categoryId, $layoutId)
    {
        $layout = app('api')->layouts()->getByHashedId($layoutId);
        $category = $this->getByHashedId($categoryId);
        $category->layout()->associate($layout);
        $category->save();

        return $category;
    }

    public function reorder(array $data)
    {
        $response = false;

        $node = $this->getByHashedId($data['node']);
        $movedNode = $this->getByHashedId($data['moved-node']);
        $action = $data['action'];

        switch ($action) {
            case 'before':
                $response = $movedNode->insertBeforeNode($node);
                break;
            case 'after':
                $response = $movedNode->insertAfterNode($node);
                break;
            case 'over':
                $response = $node->prependNode($movedNode);
                break;
        }

        return $response;
    }

    public function uniqueAttribute($key, $value, $channel = 'ecommerce', $lang = 'en')
    {
        $categories = $this->model->get();

        foreach ($categories as $category) {
            if (isset(
                $category->attribute_data[$key][$channel][$lang]
            ) &&
                $category->attribute_data[$key][$channel][$lang] == $value
            ) {
                return false;
            }
        }

        return true;
    }

    public function getCategoryTree($channel = null, $depth = null)
    {
        $qb = Category::channel($channel)
            ->with([
                'assets',
                'assets.transforms',
                'assets.transforms.asset',
                'assets.transforms.asset.source',
                'layout',
                'assets.source',
                'layout',
                'routes',
            ])
            ->withCount('products')
            ->defaultOrder();

        if ($depth) {
            $qb = $qb->withDepth()->having('depth', '<=', $depth);
        }

        return $qb->get()
            ->toTree();
    }

    /**
     * Deletes a resource by its given hashed ID.
     *
     * @param  string $id
     *
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return bool
     */
    public function delete($id)
    {
        $category = $this->getByHashedId($id);

        // Remove all associations
        foreach ($category->children as $child) {
            $child->customerGroups()->detach();
            $child->products()->sync([]);
            $child->customerGroups()->sync([]);
            $child->channels()->sync([]);
            $child->delete();
        }

        $category->customerGroups()->detach();
        $category->products()->sync([]);
        $category->customerGroups()->sync([]);
        $category->channels()->sync([]);

        return $category->delete();
    }

    public function getSearchedIds($ids = [])
    {
        $parsedIds = [];
        foreach ($ids as $hash) {
            $parsedIds[] = $this->model->decodeId($hash);
        }

        $placeholders = implode(',', array_fill(0, count($parsedIds), '?')); // string for the query

        $query = $this->model->with([
            'routes',
            'products',
            'assets',
            'assets',
            'primaryAsset.transforms',
            'primaryAsset.source',
            'primaryAsset',
        ])
            ->withoutGlobalScopes()
            ->whereIn('id', $parsedIds);

        if (count($parsedIds)) {
            $query = $query->orderByRaw("field(id,{$placeholders})", $parsedIds);
        }

        return $query->get();
    }
}
