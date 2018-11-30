<?php

namespace GetCandy\Api\Core\Traits;

use Hashids\Hashids as HashidsEncoder;

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

    public function encode($id)
    {
        return app('hashids')->connection($this->hashids)->encode($id);
    }

    public function encodedId()
    {
        return app('hashids')->connection($this->hashids)->encode($this->id);
    }
}
