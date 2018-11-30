<?php

namespace GetCandy\Api\Core\Search\Services;

use Illuminate\Database\Eloquent\Model;
use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Search\Models\SavedSearch;

class SavedSearchService extends BaseService
{
    public function __construct()
    {
        $this->model = new SavedSearch;
    }

    /**
     * Gets the saved searches for a model.
     *
     * @param mixed $model
     *
     * @return Collection
     */
    public function getByType($type)
    {
        return $this->model->whereType($type)->get();
    }

    /**
     * Stores a saved search.
     *
     * @param array $data
     *
     * @return void
     */
    public function store($data)
    {
        $payload = [];

        if (! empty($data['keywords'])) {
            $payload['keywords'] = $data['keywords'];
        }

        if (! empty($data['filters'])) {
            $payload['filters'] = $data['filters'];
        }

        $search = new SavedSearch;
        $search->payload = $payload;
        $search->type = $data['type'];
        $search->name = $data['keywords'];

        $search->save();

        return $search;
    }

    public function delete($id)
    {
        $search = $this->getByHashedId($id);
        if (! $search) {
            abort(404);
        }

        return $search->delete();
    }
}
