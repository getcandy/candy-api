<?php

namespace GetCandy\Api\Http\Resources\Files;

use GetCandy\Api\Http\Resources\AbstractResource;

class PdfResource extends AbstractResource
{
    public function payload()
    {
        return [
            'encoding' => 'base64',
            'contents' => base64_encode($this->output()),
        ];
    }
}
