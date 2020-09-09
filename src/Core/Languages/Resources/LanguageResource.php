<?php

namespace GetCandy\Api\Core\Languages\Resources;

use GetCandy\Api\Http\Resources\AbstractResource;

class LanguageResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'name' => $this->name,
            'lang' => $this->lang,
            'iso' => $this->iso,
            'default' => (bool) $this->default,
            'enabled' => (bool) $this->enabled,
            'current' => (bool) $this->current,
        ];
    }

    public function includes()
    {
        return [];
    }
}
