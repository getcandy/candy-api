<?php

namespace GetCandy\Api\Core\Search\Drivers;

abstract class AbstractSearchDriver
{
    protected $reference;

    public function onReference($reference)
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * Returns the config for the driver
     *
     * @return  array
     */
    abstract function config();

    abstract function index($documents, $final = false);

    abstract function update($documents);

    /**
     * Checks if a feature is available for this driver
     *
     * @param   string  $check
     *
     * @return  boolean
     */
    public function hasFeature($check)
    {
        return !!($this->config()['features'][$check] ?? false);
    }
}