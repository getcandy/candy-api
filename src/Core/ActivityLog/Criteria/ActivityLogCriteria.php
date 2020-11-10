<?php

namespace GetCandy\Api\Core\ActivityLog\Criteria;

use GetCandy\Api\Core\ActivityLog\Interfaces\ActivityLogCriteriaInterface;
use GetCandy\Api\Core\Scaffold\AbstractCriteria;
use Spatie\Activitylog\Models\Activity;

class ActivityLogCriteria extends AbstractCriteria implements ActivityLogCriteriaInterface
{
    /**
     * The model to query on.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    public function getBuilder()
    {
        return Activity::forSubject($this->model)
            ->with('causer.customer')
            ->orderBy('created_at', 'desc');
    }
}
