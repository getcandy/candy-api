<?php

namespace GetCandy\Api\Core\Payments\Exceptions;

use Exception;

class TransactionAmountException extends Exception
{
    public function __construct()
    {
        parent::__construct();
        $this->message = trans('getcandy::exceptions.transaction_amount');
    }
}
