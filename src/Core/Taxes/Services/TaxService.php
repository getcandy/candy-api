<?php

namespace GetCandy\Api\Core\Taxes\Services;

use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Taxes\Models\Tax;

class TaxService extends BaseService
{
    public function __construct()
    {
        $this->model = new Tax();
    }

    /**
     * Creates a resource from the given data.
     *
     * @param  array  $data
     * @return \GetCandy\Api\Core\Taxes\Models\Tax
     */
    public function create($data)
    {
        $tax = new Tax();
        $tax->name = $data['name'];
        $tax->percentage = $data['percentage'];

        if (empty($data['default']) && ! $this->count()) {
            $tax->default = true;
        } else {
            $tax->default = false;
        }

        if (! empty($data['default'])) {
            $this->setNewDefault($tax);
        }

        $tax->save();

        return $tax;
    }

    public function getByName($name)
    {
        return $this->model->where('name', '=', $name)->firstOrFail();
    }

    /**
     * Updates a resource from the given data.
     *
     * @param  string  $id
     * @param  array  $data
     * @return \GetCandy\Api\Core\Taxes\Models\Tax
     *
     * @throws \Exception
     */
    public function update($id, array $data)
    {
        $tax = $this->getByHashedId($id);

        if (! $tax) {
            abort(404);
        }

        if (! empty($data['default'])) {
            $this->setNewDefault($tax);
        }

        $tax->fill($data);
        $tax->save();

        return $tax;
    }

    /**
     * Deletes a resource by its given hashed ID.
     *
     * @param  string  $id
     * @return bool
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \GetCandy\Api\Exceptions\MinimumRecordRequiredException
     */
    public function delete($id)
    {
        $tax = $this->getByHashedId($id);

        if (! $tax) {
            abort(404);
        }

        if ($tax->default) {
            $newDefault = $this->getNewSuggestedDefault();
            $this->setNewDefault($newDefault);
            $newDefault->save();
        }

        return $tax->delete();
    }

    protected function setNewDefault(&$model)
    {
        if ($current = $this->getDefaultRecord()) {
            $current->default = false;
            $current->save();
        }
        $model->default = true;
    }
}
