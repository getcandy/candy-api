<?php

namespace GetCandy\Api\Core\Payments\Exceptions;

use Exception;

class InvalidPaymentTokenException extends Exception
{
    public function __construct()
    {
        $this->message = trans('getcandy::exceptions.payment_token');
    }
}
