<?php

namespace GetCandy\Api\Core\Helpers;

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
