<?php

namespace GetCandy\Api\Core\Payments\Exceptions;

use Exception;
use GetCandy\Api\Core\Payments\ThreeDSecureResponse;

class ThreeDSecureRequiredException extends Exception
{
    protected $response;

    public function __construct(ThreeDSecureResponse $response)
    {
        $this->message = trans('getcandy::exceptions.3d_secure_required');
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }
}
