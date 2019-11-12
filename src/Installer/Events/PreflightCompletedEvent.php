<?php

namespace GetCandy\Api\Installer\Events;

class PreflightCompletedEvent
{
    public $response;

    public function __construct($response)
    {
        $this->response = $response;
    }
}
