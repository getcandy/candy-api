<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Search;

use GetCandy\Api\Routes\Models\Route;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use Illuminate\Database\Eloquent\Model;

class SettingTransformer extends BaseTransformer
{
    public function transform(Model $model)
    {
        return [
            'name' => $model->name,
            'handle' => $model->handle,
            'data' => $model->content,
        ];
    }
}
