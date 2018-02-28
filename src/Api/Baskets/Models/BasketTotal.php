<?php

namespace GetCandy\Api\Baskets\Models;

class BasketTotal
{
    public $amount;

    public $tax;

    public function __construct($amount, $tax)
    {
        $this->amount = $amount;
        $this->tax = $tax;
    }
}
