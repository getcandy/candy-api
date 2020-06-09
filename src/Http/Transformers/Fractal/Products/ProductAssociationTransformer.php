<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Products;

use GetCandy\Api\Core\Products\Models\ProductAssociation;
use GetCandy\Api\Http\Transformers\Fractal\Associations\AssociationGroupTransformer;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class ProductAssociationTransformer extends BaseTransformer
{
    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = [
        'association', 'type',
    ];

    public function transform(ProductAssociation $model)
    {
        return [
            'id' => $model->encodedId(),
        ];
    }

    public function includeAssociation(ProductAssociation $model)
    {
        return $this->item($model->association, new ProductTransformer);
    }

    public function includeType(ProductAssociation $model)
    {
        return $this->item($model->group, new AssociationGroupTransformer);
    }
}
