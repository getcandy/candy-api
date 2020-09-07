<?php

namespace GetCandy\Api\Core\Scaffold;

use Lorisleiva\Actions\Action;

abstract class AbstractAction extends Action
{
    public function resolveEagerRelations()
    {
        return $this->convertPassableStringToArray($this->include);
    }

    public function resolveRelationCounts()
    {
        return $this->convertPassableStringToArray($this->counts);
    }

    /**
     * @param Action $action
     * @return static
     * See https://github.com/lorisleiva/laravel-actions/issues/57
     */
    public static function createFrom(Action $action)
    {
        return (new static)->fill($action->all())->actingAs($action->user());
    }

    private function convertPassableStringToArray($string)
    {
        $includes = $string ?: [];

        if ($includes && is_string($includes)) {
            $includes = explode(',', $includes);
        }

        return array_map(function ($inc) {
            return lcfirst(implode(array_map(function ($str) {
                return ucfirst($str);
            }, explode('_', $inc))));
        }, $includes);
    }
}
