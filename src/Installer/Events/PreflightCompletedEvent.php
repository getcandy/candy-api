<?php

namespace GetCandy\Api\Installer\Events;

class PreflightCompletedEvent
{
    /**
     * The response to be sent.
     *
     * @var array
     */
    public $response;

    public function __construct($response)
    {
        $this->response = $response;
    }
}
