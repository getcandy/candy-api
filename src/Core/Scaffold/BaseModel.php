<?php

namespace GetCandy\Api\Core\Scaffold;

use GetCandy\Api\Core\Traits\Hashids;
use Illuminate\Database\Eloquent\Model;
use GetCandy\Api\Core\Routes\Models\Route;

abstract class BaseModel extends Model
{
    use Hashids;

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
}
