<?php

namespace GetCandy\Api\Core\Languages\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class Language extends BaseModel
{
    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
    protected $hashids = 'language';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lang', 'iso', 'name', 'default', 'enabled',
    ];

    public function scopeDefault($query)
    {
        return $query->whereDefault(true);
    }

    public function scopeEnabled($query)
    {
        return $query->whereEnabled(true);
    }

    public function scopeDisabled($query)
    {
        return $query->whereEnabled(false);
    }

    public function scopeLang($query, $lang)
    {
        if (! is_array($lang)) {
            $lang = [$lang];
        }

        return $query->whereIn('lang', $lang);
    }

    public function scopeCode($query, $code)
    {
        return $query->whereIso($code);
    }
}
