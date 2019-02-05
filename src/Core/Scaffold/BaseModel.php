<?php

namespace GetCandy\Api\Core\Scaffold;

use GetCandy\Api\Core\Traits\Hashids;
use Illuminate\Database\Eloquent\Model;
use GetCandy\Api\Core\Routes\Models\Route;

abstract class BaseModel extends Model
{
    use Hashids;

    protected $hashids = 'main';

    public $custom_attributes = [];

    public function getSettingsAttribute()
    {
        $settings = app('api')->settings()->get($this->settings);
        if (! $settings) {
            return [];
        }

        return $settings->content;
    }

    public function setCustomAttribute($key, $value)
    {
        $this->custom_attributes[$key] = $value;

        return $this;
    }

    public function getCustomAttribute($key)
    {
        return $this->custom_attributes[$key] ?? null;
    }

    /**
     * Scope a query to only include enabled.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', '=', true);
    }

    /**
     * Scope a query to only include the default record.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefault($query)
    {
        return $query->where('default', '=', true);
    }

    public function routes()
    {
        return $this->morphMany(Route::class, 'element');
    }

    /**
     * Determine if the given relationship (method) exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasRelation($key)
    {
        // If the key already exists in the relationships array, it just means the
        // relationship has already been loaded, so we'll just return it out of
        // here because there is no need to query within the relations twice.
        if ($this->relationLoaded($key)) {
            return true;
        }

        // If the "attribute" exists as a method on the model, we will just assume
        // it is a relationship and will load and return results from the query
        // and hydrate the relationship's value on the "relationships" array.
        if (method_exists($this, $key)) {
            //Uses PHP built in function to determine whether the returned object is a laravel relation
            return is_a($this->$key(), "Illuminate\Database\Eloquent\Relations\Relation");
        }

        return false;
    }
}
