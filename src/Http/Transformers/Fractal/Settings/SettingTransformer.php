<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Settings;

use Illuminate\Database\Eloquent\Model;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

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
