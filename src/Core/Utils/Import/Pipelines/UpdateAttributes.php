<?php

namespace GetCandy\Api\Core\Utils\Import\Pipelines;

use Closure;
use GetCandy\Api\Core\Utils\Import\PipesContract;

class UpdateAttributes implements PipesContract
{
    public function handle($data, Closure $next)
    {
        $model = $data[0];
        $line = $data[1];

        $attributeData = $model->product->attribute_data;

        foreach ($attributeData as $handle => $channels) {
            foreach ($channels as $channel => $locales) {
                foreach ($locales as $locale => $value) {
                    $newValue = $line->{$handle};
                    if ($newValue) {
                        $attributeData[$handle][$channel][$locale] = $newValue;
                    }
                }
            }
        }

        $model->product->update([
            'attribute_data' => $attributeData,
        ]);

        return $next([$model, $line, $data[2]]);
    }
}
