<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Assets;

use GetCandy\Api\Assets\Models\Transform;
use League\Fractal\TransformerAbstract;

class TransformTransformer extends TransformerAbstract
{
    public function transform(Transform $transform)
    {
        return [
            'handle' => $transform->handle,
            'name' => $transform->name,
            'width' => $transform->width,
            'height' => $transform->height,
            'mode' => $transform->mode,
            'format' => $transform->format,
            'position' => $transform->position,
            'constraint' => $transform->constraint
        ];
    }
}