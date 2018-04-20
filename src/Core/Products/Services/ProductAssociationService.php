<?php

namespace GetCandy\Api\Core\Products\Services;

use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Products\Models\ProductAssociation;

class ProductAssociationService extends BaseService
{
    protected $associations;

    public function __construct()
    {
        $this->model = new Product;
        $this->associations = new ProductAssociation;
    }

    /**
     * Stores a product association.
     * @param  string $product
     * @param  array $data
     * @return mixed
     */
    public function store($product, $data)
    {
        $product = $this->getByHashedId($product);

        $product->associations()->delete();

        foreach ($data['relations'] as $index => $relation) {
            $relation['association'] = $this->getByHashedId($relation['association_id']);
            $relation['type'] = app('api')->associationGroups()->getByHashedId($relation['type']);
            $assoc = new ProductAssociation;
            $assoc->group()->associate($relation['type']);
            $assoc->association()->associate($relation['association']);
            $assoc->parent()->associate($product);
            $assoc->save();
        }

        return $product->associations;
    }

    /**
     * Destroys product association/s.
     * @param  string $product
     * @param  array/string $association
     * @return bool
     */
    public function destroy($product, $association)
    {
        $product = $this->getByHashedId($product);

        if (is_array($association)) {
            $ref = $this->getDecodedIds($association);
            $product->associations()->whereIn('association_id', $ref)->get()->delete();
        } else {
            $ref = $this->getDecodedId($association);
            $product->associations()->where('association_id', '=', $ref)->first()->delete();
        }

        return true;
    }
}
