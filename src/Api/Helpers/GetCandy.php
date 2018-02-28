<?php

namespace GetCandy\Api\Helpers;

use Illuminate\Support\Manager;


class GetCandy
{
    protected $groups = [];

    public function setGroups($groups)
    {
        $this->groups = $groups;
        return $this;
    }

    public function getGroups()
    {
        return $this->groups;
    }
}
