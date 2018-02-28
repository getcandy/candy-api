<?php

namespace GetCandy\Api\Assets\Models;

use GetCandy\Api\Categories\Models\Category;
use GetCandy\Api\Products\Models\Product;
use GetCandy\Api\Scaffold\BaseModel;
use GetCandy\Api\Tags\Models\Tag;

class Asset extends BaseModel
{
    protected $hashids = 'main';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'caption',
        'size',
        'extension',
        'filename',
        'original_filename',
        'sub_kind',
        'width',
        'location',
        'height',
        'kind',
        'position',
        'external',
        'primary'
    ];

    /**
     * Get all of the owning commentable models.
     */
    public function assetable()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function source()
    {
        return $this->belongsTo(AssetSource::class, 'asset_source_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function transforms()
    {
        return $this->hasMany(AssetTransform::class);
    }

    public function thumbnail()
    {
        return $this->transforms()->whereHas('transform', function ($q) {
            $q->whereHandle('thumbnail');
        });

    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function products()
    {
        return $this->morphedByMany(Product::class, 'assetable');
    }

    public function categories()
    {
        return $this->morphedByMany(Category::class, 'assetable');
    }

    public function uploader()
    {
        return app('api')->assets()->getDriver($this->kind);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
