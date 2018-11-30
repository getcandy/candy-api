<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Languages;

use GetCandy\Api\Core\Languages\Models\Language;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class LanguageTransformer extends BaseTransformer
{
    protected $availableIncludes = [];

    public function transform(Language $language)
    {
        return [
            'id' => $language->encodedId(),
            'name' => $language->name,
            'lang' => $language->lang,
            'iso' => $language->iso,
            'default' => $language->default,
            'enabled' => $language->enabled,
            'current' => (bool) ($language->code == app()->getLocale()),
        ];
    }
}
