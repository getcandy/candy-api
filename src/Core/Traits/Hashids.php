<?php

namespace GetCandy\Api\Core\Traits;

trait Hashids
{
    public function hashids()
    {
        return app('hashids')->connection($this->hashids);
    }

    public function getEncodedIdAttribute()
    {
        return app('hashids')->connection($this->hashids)->encode($this->id);
    }

    public function decodeId($value)
    {
        $result = app('hashids')->connection($this->hashids)->decode($value);

        return empty($result[0]) ? null : $result[0];
    }

    public function decodeIds($value)
    {
        $ids = [];

        foreach ($value as $id) {
            $realId = app('hashids')->connection($this->hashids)->decode($id);
            if (! empty($realId[0])) {
                $ids[] = $realId[0];
            }
        }

        return $ids;
    }

    public function encode($id)
    {
        return app('hashids')->connection($this->hashids)->encode($id);
    }

    public function encodedId()
    {
        return app('hashids')->connection($this->hashids)->encode($this->id);
    }
}
