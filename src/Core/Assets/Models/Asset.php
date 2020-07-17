<?php

namespace GetCandy\Api\Core\Assets\Models;

use GetCandy;
use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Tags\Models\Tag;
use Storage;

class Asset extends BaseModel
{
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
        'asset_source_id',
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
        'external',
    ];

    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'url' => Storage::disk($this->source->disk)->url($this->location.'/'.$this->filename),
        ]);
    }

    public function scopeImages($query)
    {
        return $query->where('kind', '!=', 'application');
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function source()
    {
        return $this->belongsTo(AssetSource::class, 'asset_source_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
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

    public function assetable()
    {
        return $this->belongsTo(Assetable::class);
    }

    public function uploader()
    {
        return GetCandy::assets()->getDriver($this->kind);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
