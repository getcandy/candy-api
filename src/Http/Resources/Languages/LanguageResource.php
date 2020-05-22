<?php

namespace GetCandy\Api\Http\Resources\Languages;

use GetCandy\Api\Http\Resources\AbstractResource;

class LanguageResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encodedId(),
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
