<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Products;

use GetCandy\Api\Core\Products\Models\ProductAssociation;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Associations\AssociationGroupTransformer;

class ProductAssociationTransformer extends BaseTransformer
{
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
