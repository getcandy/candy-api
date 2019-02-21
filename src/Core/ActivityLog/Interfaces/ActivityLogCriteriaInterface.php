<?php

namespace GetCandy\Api\Core\ActivityLog\Interfaces;

interface ActivityLogCriteriaInterface
{
    /**
     * Gets the underlying builder for the query.
     *
     * @return \Illuminate\Database\Eloquent\QueryBuilder
     */
    public function getBuilder();
}
