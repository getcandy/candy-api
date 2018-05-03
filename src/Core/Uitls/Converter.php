<?php

namespace GetCandy\Api\Core\Utis;

class Converter
{
    protected $amount;
    protected $fromUnit;
    protected $toUnit;

    public function convert($amount, $unit)
    {
        $this->amount = $amount;
        $this->unit = $unit;

        return $this;
    }

    public function to($type)
    {
        // TODO: CREATE A WORKING CONVERSION;
    }
}
