<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Search;

use Illuminate\Database\Eloquent\Model;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class SavedSearchTransformer extends BaseTransformer
{
    public function transform(Model $model)
    {
        return [
            'id' => $model->encodedId(),
            'name' => $model->name,
            'payload' => $model->payload,
        ];
    }
}
