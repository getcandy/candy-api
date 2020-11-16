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
     * Returns the config for the driver.
     *
     * @return  array
     */
    abstract public function config();

    abstract public function index($documents, $final = false);

    abstract public function update($documents);

    /**
     * Checks if a feature is available for this driver.
     *
     * @param   string  $check
     *
     * @return  bool
     */
    public function hasFeature($check)
    {
        return (bool) ($this->config()['features'][$check] ?? false);
    }
}
