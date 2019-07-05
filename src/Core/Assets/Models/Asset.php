<?php

namespace GetCandy\Api\Core\Assets\Models;

use Storage;
use GetCandy\Api\Core\Tags\Models\Tag;
use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Categories\Models\Category;

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
        'primary',
    ];

    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'url' => Storage::disk($this->source->disk)->url($this->location.'/'.$this->filename),
        ]);
    }

    /**
     * Get the url attribute.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        if ($this->external) {
            return $this->location;
        }

        return Storage::disk($this->source->disk)->url($this->location.'/'.$this->filename);
    }

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
