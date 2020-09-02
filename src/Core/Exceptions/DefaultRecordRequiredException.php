<?php

namespace GetCandy\Api\Core\Exceptions;

class DefaultRecordRequiredException extends \Exception
{
    protected $message = 'You cannot delete a default record, make a different record default and try again.';
}
