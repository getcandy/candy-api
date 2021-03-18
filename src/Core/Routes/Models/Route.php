<?php

namespace GetCandy\Api\Core\Routes\Models;

use GetCandy\Api\Core\Languages\Models\Language;
use GetCandy\Api\Core\Scaffold\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use NeonDigital\Drafting\Draftable;

class Route extends BaseModel
{
    use SoftDeletes,
        Draftable;

    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
    protected $hashids = 'main';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'slug', 'default', 'redirect', 'description', 'element_type', 'element_id', 'language_id',
    ];

    /**
     * Get all of the owning element models.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function element()
    {
        return $this->morphTo();
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
