<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Sorts;

abstract class AbstractSort
{
    /**
     * The field to sort on.
     *
     * @var string
     */
    protected $field;

    /**
     * The sort direction.
     *
     * @var string
     */
    protected $dir;

    /**
     * Set the sort mode.
     *
     * @var string
     */
    protected $mode;

    /**
     * The reference to the sort.
     *
     * @var string
     */
    protected $handle;

    public function __construct($field = null, $handle = null, $dir = null, $mode = null)
    {
        $this->field = $field;
        $this->dir = $dir;
        $this->handle = $handle;
        $this->mode = $mode;
    }

    /**
     * Set the field value.
     *
     * @param string $field
     * @return AbstractSort
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Set the sort direction.
     *
     * @param string $dir
     * @return AbstractSort
     */
    public function setDir($dir)
    {
        $this->dir = $dir;

        return $this;
    }

    /**
     * Set the mode.
     *
     * @param string $mode
     * @return AbstractSort
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * Get the sort mapping.
     *
     * @return array
     */
    abstract public function getMapping();
}
