<?php

namespace GetCandy\Api\Http\Resources\Settings;

use GetCandy\Api\Http\Resources\AbstractResource;

class SettingResource extends AbstractResource
{
    public function payload()
    {

        return array_merge([
            'id' => $this->encodedId(),
            'name' => $this->name,
            'handle' => $this->handle,
            'content' => $this->content,
        ], $this->config ? $this->config->toArray() : []);
    }

    public function includes()
    {
        return [];
    }
}
