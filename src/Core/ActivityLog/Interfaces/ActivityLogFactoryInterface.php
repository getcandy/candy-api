<?php

namespace GetCandy\Api\Core\ActivityLog\Interfaces;

interface ActivityLogFactoryInterface
{
    /**
     * Log the action.
     *
     * @param string $type
     * @return void
     */
    public function log($type = 'default');
}
