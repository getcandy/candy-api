<?php

namespace GetCandy\Api\Core\ActivityLog\Criteria;

use Spatie\Activitylog\Models\Activity;
use GetCandy\Api\Core\Scaffold\AbstractCriteria;
use GetCandy\Api\Core\ActivityLog\Interfaces\ActivityLogCriteriaInterface;

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
            ->with('causer.details')
            ->orderBy('created_at', 'desc');
    }
}
