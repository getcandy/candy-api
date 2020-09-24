<?php

namespace GetCandy\Api\Core\Scaffold;

use Illuminate\Database\Eloquent\Builder;
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

    protected function compileSearchQuery(Builder $query, $parameters)
    {
        foreach ($parameters as $field => $value) {
            if (method_exists($query->getModel(), 'scope'.ucfirst($field))) {
                $query->{$field}($value);
                continue;
            }
            if (is_array($value)) {
                $query->whereIn($field, $value);
                continue;
            }
            $query->where($field, '=', $value);
        }

        return $query;
    }
}
