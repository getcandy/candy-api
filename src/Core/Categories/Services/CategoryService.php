<?php

namespace GetCandy\Api\Core\Categories\Services;

use GetCandy;
use GetCandy\Api\Core\Attributes\Events\AttributableSavedEvent;
use GetCandy\Api\Core\Categories\Events\CategoryStoredEvent;
use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Customers\Actions\FetchCustomerGroup;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Routes\Models\Route;
use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Search\Actions\IndexObjects;
use GetCandy\Api\Core\Search\Events\IndexableSavedEvent;

class CategoryService extends BaseService
{
    /**
     * @var \GetCandy\Api\Core\Categories\Models\Category
     */
    protected $model;

    /**
     * @var \GetCandy\Api\Core\Routes\Models\Route
     */
    protected $route;

    public function __construct()
    {
        $this->model = new Category();
        $this->route = new Route();
    }

    /**
     * Returns model by a given hashed id.
     *
     * @param  string  $id
     * @return \GetCandy\Api\Core\Categories\Models\Category
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getByHashedId($id)
    {
        $id = $this->model->decodeId($id);

        return $this->model->with($this->with)->withDepth()->withoutGlobalScopes()->findOrFail($id);
    }

    public function findById($id, array $includes = [], $draft = false)
    {
        $query = Category::with(array_merge($includes, ['draft']));

        if ($draft) {
            $query->withDrafted()->withoutGlobalScopes();
        }

        return $query->find($id);
    }

    public function getNestedList()
    {
        $categories = $this->model->withDepth()->defaultOrder()->get()->toTree();

        return $categories;
    }

    public function getByParentID($encodedParentID, $includes = null)
    {
        $parentID = $this->model->decodeId($encodedParentID);

        $query = $this->model->where('parent_id', $parentID)->defaultOrder();

        if ($includes) {
            $query->with($includes);
        }

        return $query->get();
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
        } else {
            $category->customerGroups()->sync(CustomerGroup::select('id')->get(), [
                'visible' => false,
                'purchasable' => false,
            ]);
        }

        if (! empty($data['channels']['data'])) {
            $category->channels()->sync(
                $this->getChannelMapping($data['channels']['data'])
            );
        } else {
            $category->channels()->sync(Channel::select('id')->get(), [
                'published_at' => null,
            ]);
        }

        $urls = $this->getUniqueUrl($data['url'], $data['path'] ?? null);

        $category->routes()->createMany($urls);

        // If a parent id exists then add the category to the parent
        if (! empty($data['parent']['id'])) {
            $parentNode = $this->getByHashedId($data['parent']['id']);
            $parentNode->prependNode($category);
        }

        event(new AttributableSavedEvent($category));
        event(new IndexableSavedEvent($category));
        event(new CategoryStoredEvent($category));

        return $category;
    }

    public function update($hashedId, array $data)
    {
        $model = $this->getByHashedId($hashedId, true);
        $model->attribute_data = $data['attribute_data'];

        if (! empty($data['customer_groups'])) {
            $groupData = $this->mapCustomerGroupData($data['customer_groups']['data']);
            $model->customerGroups()->sync($groupData);
        }

        if (! empty($data['layout_id'])) {
            $realLayoutId = GetCandy::layouts()->getDecodedId($data['layout_id']);
            $model->layout_id = $realLayoutId;
        }

        if (! empty($data['channels']['data'])) {
            $model->channels()->sync(
                $this->getChannelMapping($data['channels']['data'])
            );
        }

        $model->save();

        event(new AttributableSavedEvent($model));
        event(new IndexableSavedEvent($model));
        event(new CategoryStoredEvent($model));

        return $model;
    }

    public function updateChannels($id, array $data)
    {
        $category = $this->getByHashedId($id, true);
        $category->channels()->sync(
            $this->getChannelMapping($data['channels'])
        );

        return $category;
    }

    public function updateCustomerGroups($id, array $groups)
    {
        $category = $this->getByHashedId($id, true);
        $groupData = [];
        foreach ($groups as $group) {
            $groupModel = FetchCustomerGroup::run([
                'encoded_id' => $group['id'],
            ]);
            $groupData[$groupModel->id] = [
                'visible' => $group['visible'],
                'purchasable' => $group['purchasable'],
            ];
        }
        $category->customerGroups()->sync($groupData);
        $category->load('customerGroups');

        return $category;
    }

    public function updateProducts($id, array $data)
    {
        $category = $this->getByHashedId($id);
        $category->sort = $data['sort_type'];
        $category->save();

        $existingProducts = $category->products;

        $ids = [];

        foreach ($data['products'] as $item) {
            $ids[GetCandy::products()->getDecodedId($item['id'])] = ['position' => $item['position']];
        }

        $category->products()->sync($ids);

        if ($existingProducts->count()) {
            IndexObjects::run([
                'documents' => $existingProducts,
            ]);
        }

        IndexObjects::run([
            'documents' => $category->products()->get(),
        ]);

        event(new CategoryStoredEvent($category));

        return $category;
    }

    /**
     * Update a category layout.
     *
     * @param  string  $categoryId
     * @param  string  $layoutId
     * @return \GetCandy\Api\Core\Categories\Models\Category
     */
    public function updateLayout($categoryId, $layoutId)
    {
        $layout = GetCandy::layouts()->getByHashedId($layoutId);
        $category = $this->getByHashedId($categoryId);
        $category->layout()->associate($layout);
        $category->save();

        event(new CategoryStoredEvent($category));

        return $category;
    }

    public function reorder(array $data)
    {
        $response = false;

        $node = $this->getByHashedId($data['node'], true);
        $movedNode = $this->getByHashedId($data['moved-node'], true);
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

        event(new CategoryStoredEvent($node));

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

    public function getCategoryTree($channel = null, $depth = 1, $includes = null)
    {
        return Category::channel($channel)
            ->with()
            ->withCount('products')
            ->defaultOrder()
            ->withDepth()
            ->having('depth', '<=', $depth)
            ->get()
            ->toTree();
    }

    /**
     * Deletes a resource by its given hashed ID.
     *
     * @param  string  $id
     * @return bool
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
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

        event(new CategoryStoredEvent($category));

        return $category->delete();
    }

    public function getSearchedIds($ids = [], $includes = [])
    {
        $parsedIds = [];
        foreach ($ids as $hash) {
            $parsedIds[] = $this->model->decodeId($hash);
        }

        $placeholders = implode(',', array_fill(0, count($parsedIds), '?')); // string for the query

        $query = $this->model->with($includes)
            ->withoutGlobalScopes()
            ->whereIn('id', $parsedIds);

        if (count($parsedIds)) {
            $query = $query->orderByRaw("field(id,{$placeholders})", $parsedIds);
        }

        return $query->get();
    }
}
