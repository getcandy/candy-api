<?php

namespace GetCandy\Api\Core\ActivityLog\Factories;

use Illuminate\Database\Eloquent\Model;
use GetCandy\Api\Core\ActivityLog\Interfaces\ActivityLogFactoryInterface;

class ActivityLogFactory implements ActivityLogFactoryInterface
{
    /**
     * The related model.
     *
     * @var Model
     */
    protected $model;

    /**
     * The action taken.
     *
     * @var string
     */
    protected $action;

    /**
     * Additional properties to send.
     *
     * @var array
     */
    protected $properties = [];

    /**
     * The user who commited the action.
     *
     * @var string
     */
    protected $user;

    /**
     * Set the model.
     *
     * @param Model $model
     * @return self
     */
    public function against(Model $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Set the value for action.
     *
     * @param string $action
     * @return self
     */
    public function action($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Set any additional properties.
     *
     * @param array $properties
     * @return self
     */
    public function with($properties = [])
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * Set the value for user.
     *
     * @param Model $user
     * @return self
     */
    public function as(Model $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Log the action.
     *
     * @param string $type
     * @return void
     */
    public function log($type = 'default')
    {
        activity($type)
            ->causedBy($this->user)
            ->performedOn($this->model)
            ->withProperties($this->properties)
            ->log($this->action);
    }
}
