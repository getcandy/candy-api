<?php

namespace GetCandy\Api\Core\Baskets\Models;

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
