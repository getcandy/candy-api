<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Pages;

use GetCandy\Api\Core\Pages\Models\Page;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class PageTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'element',
    ];

    public function transform(Page $page)
    {
        return [
            'id' => $page->encodedId(),
            'slug' => $page->slug,
            'seo_title' => $page->seo_title,
            'seo_description' => $page->seo_description,
        ];
    }

    public function includeElement(Page $page)
    {
        return $this->item($page->element, new $page->element->transformer);
    }
}
