<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Sorts;

use GetCandy\Api\Core\Customers\Actions\FetchDefaultCustomerGroup;
use Illuminate\Database\Eloquent\Model;

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

    /**
     * The authenticated user.
     *
     * @var \Illuminate\Foundation\Auth\User
     */
    protected $user = null;

    public function __construct($field = null, $handle = null, $dir = null, $mode = null)
    {
        $this->field = $field;
        $this->dir = $dir;
        $this->handle = $handle;
        $this->mode = $mode;
    }

    /**
     * Set the user on the sort.
     *
     * @param  \Illuminate\Foundation\Auth\User  $user
     * @return $this
     */
    public function user(Model $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Set the field value.
     *
     * @param  string  $field
     * @return $this
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Set the sort direction.
     *
     * @param  string  $dir
     * @return $this
     */
    public function setDir($dir)
    {
        $this->dir = $dir;

        return $this;
    }

    /**
     * Set the mode.
     *
     * @param  string  $mode
     * @return $this
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

    /**
     * Get the customer groups.
     *
     * @return array
     */
    protected function customerGroups()
    {
        if ($this->user) {
            // Set to empty array as we don't want to filter any out.
            if ($this->user->hasRole('admin')) {
                $groups = [];
            } else {
                $groups = $this->user->groups;
            }
        } else {
            $groups = [FetchDefaultCustomerGroup::run()];
        }

        return $groups;
    }
}
