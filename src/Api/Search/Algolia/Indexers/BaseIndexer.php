<?php

namespace GetCandy\Api\Search\Algolia\Indexers;

use Illuminate\Database\Eloquent\Model;

abstract class BaseIndexer
{

    public function attributeMapping(Model $model)
    {
        $mapping = [];
        foreach ($model->attribute_data as $field => $channel) {
            foreach ($channel as $channelName => $locales) {
                foreach ($locales as $locale => $value) {
                    $mapping[$model->id][$locale]['index'] = config('search.index_prefix') . '_' . $this->type . '_' . $locale;
                    $mapping[$model->id][$locale]['data']['id'] = $model->encodedId();
                    $mapping[$model->id][$locale]['data'][$field] = strip_tags($model->attribute($field, $channelName, $locale));
                }
            }
        }
        return $mapping;
    }
}
