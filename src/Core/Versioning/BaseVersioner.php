<?php

namespace GetCandy\Api\Core\Versioning;

use Illuminate\Support\Facades\Log;

abstract class BaseVersioner
{
    protected $extendedVersionActions = [];
    protected $extendedRestoreActions = [];

    public function addVersionAction($action)
    {
        return $this->addAction('extendedVersionActions', $action);
    }

    public function addRestoreAction($action)
    {
        return $this->addAction('extendedRestoreActions', $action);
    }

    protected function addAction($target, $incoming)
    {
        if (is_array($incoming)) {
            $this->{$target} = array_merge($this->{$target}, $incoming);

            return;
        }
        array_push($this->{$target}, $incoming);
    }

    protected function callActions(array $actions, array $params = [])
    {
        foreach ($actions as $action) {
            if (! class_exists($action)) {
                Log::error("Tried to call action ${action} but it doesn't exist");

                continue;
            }
            call_user_func("{$action}::run", $params);
        }
    }
}
