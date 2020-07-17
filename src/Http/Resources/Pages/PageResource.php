<?php

namespace GetCandy\Api\Http\Resources\Pages;

use GetCandy\Api\Http\Resources\AbstractResource;

class PageResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'slug' => $this->slug,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
        ];
    }
}
