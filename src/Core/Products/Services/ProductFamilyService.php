<?php

namespace GetCandy\Api\Core\Products\Services;

use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Products\Models\ProductFamily;

class ProductFamilyService extends BaseService
{
    public function __construct()
    {
        $this->model = new ProductFamily();
    }

    /**
     * Creates a resource from the given data.
     *
     * @param array $data
     *
     * @return GetCandy\Api\Core\Models\ProductFamily
     */
    public function create(array $data)
    {
        $family = $this->model;
        $family->attribute_data = $data;
        $family->save();

        return $family;
    }

    /**
     * Updates a resource from the given data.
     *
     * @param  string $hashedId
     * @param  array  $data
     *
     * @throws Symfony\Component\HttpKernel\Exception
     *
     * @return GetCandy\Api\Core\Models\ProductFamily
     */
    public function update($hashedId, array $data)
    {
        $family = $this->getByHashedId($hashedId);
        $family->attribute_data = $data;
        $family->save();

        return $family;
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
    public function delete($hashedId, $target = null)
    {
        $productFamily = $this->getByHashedId($hashedId);
        if (! $productFamily) {
            abort(404);
        }

        if (! $target) {
            $target = $this->getDefaultRecord()->id;
        } else {
            $target = $this->getDecodedId($target);
        }

        $products = $productFamily->products()->select('id')->get()->toArray();

        \DB::table('products')->whereIn('id', $products)->update(['product_family_id' => $target]);

        return $productFamily->delete();
    }
}
