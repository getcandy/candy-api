<?php

namespace GetCandy\Api\Core\RecycleBin\Traits;

use GetCandy\Api\Core\RecycleBin\Models\RecycleBin;
use Illuminate\Database\Eloquent\SoftDeletes;

trait Recyclable
{
    use SoftDeletes;

    public static function bootRecyclable()
    {
        static::deleting(function ($model) {
            if (! $model->isForceDeleting()) {
                $model->recycleBin()->firstOrCreate([
                    'recyclable_type' => get_class($model),
                    'recyclable_id' => $model->id,
                ]);
            }
        });

        static::restored(function ($model) {
            $model->recycleBin()->delete();
        });

        static::deleted(function ($model) {
            if ($model->isForceDeleting()) {
                $model->recycleBin()->delete();
            }
        });
    }

    public function recycleBin()
    {
        return $this->morphOne(RecycleBin::class, 'recyclable');
    }

    abstract public function getRecycleName();

    abstract public function getRecycleThumbnail();
}
