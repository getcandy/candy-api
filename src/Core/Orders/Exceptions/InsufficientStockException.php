<?php

namespace GetCandy\Api\Core\Orders\Exceptions;

use Exception;
use GetCandy\Api\Http\Resources\Baskets\BasketLineResource;

class InsufficientStockException extends Exception
{
    protected $basketLines;

    public function __construct($basketLines)
    {
        $lineCount = collect($basketLines)->count();
        $this->message = "Insufficient stock for {$lineCount} basket lines";
        $this->basketLines = $basketLines;
    }

    public function getBasketLines()
    {
        return collect($this->basketLines)->map(function ($line) {
            return new BasketLineResource($line);
        });
    }
}
