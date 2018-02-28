<?php

namespace GetCandy\Api\Collections\Services;

use Carbon\Carbon;
use GetCandy\Api\Collections\Models\Collection;
use GetCandy\Api\Scaffold\BaseService;
use GetCandy\Exceptions\MinimumRecordRequiredException;
use GetCandy\Api\Attributes\Events\AttributableSavedEvent;

class CollectionService extends BaseService
{
    /**
     * @var AttributeGroup
     */
    protected $model;

    public function __construct()
    {
        $this->model = new Collection();
    }

    /**
     * Creates a resource from the given data
     *
     * @param  array  $data
     *
     * @return Collection
     */
    public function create(array $data)
    {
        $collection = $this->model;
        $collection->attribute_data = $data;
        $collection->save();

        $urls = $this->getUniqueUrl($data['url']);

        $collection->routes()->createMany($urls);

        event(new AttributableSavedEvent($collection));

        return $collection;
    }

    /**
     * Deletes a resource by its given hashed ID
     *
     * @param  string $id
     *
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Boolean
     */
    public function delete($id)
    {
        $collection = $this->getByHashedId($id);
        return $collection->delete();
    }


    /**
     * Gets paginated data for the record
     * @param  integer $length How many results per page
     * @param  int  $page   The page to start
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPaginatedData($searchTerm = null, $length = 50, $page = null)
    {
        if ($searchTerm) {
            $ids = app(SearchContract::class)->against(get_class($this->model))->with($searchTerm);
            $results = $this->model->whereIn('id', $ids);
        } else {
            $results = $this->model;
        }
        return $results->paginate($length, ['*'], 'page', $page);
    }
}
