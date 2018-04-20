<?php

namespace GetCandy\Api\Core\Traits;

trait Hashids
{
    public function hashids()
    {
        return app('hashids')->connection($this->hashids);
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
