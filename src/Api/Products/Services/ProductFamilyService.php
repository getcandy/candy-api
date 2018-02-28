<?php

namespace GetCandy\Api\Products\Services;

use GetCandy\Api\Products\Models\ProductFamily;
use GetCandy\Api\Scaffold\BaseService;
use GetCandy\Exceptions\InvalidLanguageException;

class ProductFamilyService extends BaseService
{
    public function __construct()
    {
        $this->model = new ProductFamily();
    }

    /**
     * Creates a resource from the given data
     *
     * @param array $data
     *
     * @return GetCandy\Api\Models\ProductFamily
     */
    public function create(array $data)
    {
        $family = $this->model;
        $family->attribute_data = $data;
        $family->save();
        return $family;
    }

    /**
     * Updates a resource from the given data
     *
     * @param  string $hashedId
     * @param  array  $data
     *
     * @throws Symfony\Component\HttpKernel\Exception
     *
     * @return GetCandy\Api\Models\ProductFamily
     */
    public function update($hashedId, array $data)
    {
        $family = $this->getByHashedId($hashedId);
        $family->attribute_data = $data['attributes'];
        $family->save();
        return $family;
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
    public function delete($hashedId)
    {
        $productFamily = $this->getByHashedId($hashedId);
        if (!$productFamily) {
            abort(404);
        }
        return $productFamily->delete();
    }
}
