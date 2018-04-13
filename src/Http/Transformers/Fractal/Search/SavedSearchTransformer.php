<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Search;

use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use Illuminate\Database\Eloquent\Model;

class SavedSearchTransformer extends BaseTransformer
{
    public function transform(Model $model)
    {
        return [
            'id'      => $model->encodedId(),
            'name'    => $model->name,
            'payload' => $model->payload,
        ];
    }
}
