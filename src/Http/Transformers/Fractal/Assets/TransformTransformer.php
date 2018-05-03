<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Assets;

use League\Fractal\TransformerAbstract;
use GetCandy\Api\Core\Assets\Models\Transform;

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
            'constraint' => $transform->constraint,
        ];
    }
}
