<?php

namespace GetCandy\Api\Core\Collections\Services;

use GetCandy;
use GetCandy\Api\Core\Attributes\Events\AttributableSavedEvent;
use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Collections\Models\Collection;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Search\Actions\FetchSearchedIds;
use GetCandy\Api\Core\Search\Actions\Search;

class CollectionService extends BaseService
{
    /**
     * @var \GetCandy\Api\Core\Collections\Models\Collection
     */
    protected $model;

    public function __construct()
    {
        $this->model = new Collection();
    }

    /**
     * Returns model by a given hashed id.
     *
     * @param  string  $id
     * @param  bool  $withDrafted
     * @return \GetCandy\Api\Core\Collections\Models\Collection
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getByHashedId($id, $withDrafted = false)
    {
        $id = $this->model->decodeId($id);
        $collection = $this->model;

        if ($withDrafted) {
            $collection = $collection->withDrafted();
        }

        return $collection->findOrFail($id);
    }

    public function findById($id, array $includes = [], $draft = false)
    {
        $query = Collection::with(array_merge($includes, ['draft']));

        if ($draft) {
            $query->withDrafted();
        }

        return $query->find($id);
    }

    /**
     * Creates a resource from the given data.
     *
     * @param  array  $data
     * @return \GetCandy\Api\Core\Collections\Models\Collection
     */
    public function create(array $data)
    {
        $collection = $this->model;
        $collection->attribute_data = $data;
        $collection->save();

        $urls = $this->getUniqueUrl($data['url']);

        $collection->routes()->createMany($urls);

        // event(new AttributableSavedEvent($collection));

        return $collection;
    }

    /**
     * Deletes a resource by its given hashed ID.
     *
     * @param  string  $id
     * @return bool|null
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function delete($id)
    {
        $collection = $this->getByHashedId($id, true);

        $collection->customerGroups()->detach();
        $collection->channels()->detach();
        $collection->products()->detach();

        return $collection->delete();
    }

    /**
     * Gets paginated data for the record.
     *
     * @param  string  $searchTerm
     * @param  int  $length
     * @param  null|int  $page
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPaginatedData($searchTerm = null, $length = 50, $page = null)
    {
        if ($searchTerm) {
            $type = get_class($this->model) == Product::class ? 'products' : 'categories';
            // set type
            $search = Search::run([
                'params' =>[
                    'type' => $type,
                    'term' => 'test',
                ],
            ]);

            $ids = collect();
            $results = collect($search->getResults());

            if ($results->count()) {
                foreach ($results as $r) {
                    $ids->push($r->getId());
                }
            }

            $results = FetchSearchedIds::run([
                'model' => $type == 'products' ? Product::class : Category::class,
                'encoded_ids' => $ids->toArray(),
            ]);
        } else {
            $results = $this->model;
        }

        return $results->paginate($length, ['*'], 'page', $page);
    }

    /**
     * Sync products to a collection.
     *
     * @param  string  $collectionId
     * @param  array  $products
     * @return \GetCandy\Api\Core\Collections\Models\Collection
     */
    public function syncProducts($collectionId, $products = [])
    {
        $collection = $this->getByHashedId($collectionId);
        $productIds = GetCandy::products()->getDecodedIds($products);
        $collection->products()->withTimestamps()->sync($productIds);

        return $collection;
    }
}
