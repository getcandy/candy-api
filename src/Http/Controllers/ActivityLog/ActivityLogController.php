<?php

namespace GetCandy\Api\Http\Controllers\ActivityLog;

use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Core\Orders\Services\OrderService;
use GetCandy\Api\Http\Resources\ActivityLog\ActivityCollection;
use GetCandy\Api\Core\ActivityLog\Interfaces\ActivityLogFactoryInterface;
use GetCandy\Api\Core\ActivityLog\Interfaces\ActivityLogCriteriaInterface;

class ActivityLogController extends BaseController
{
    protected $types = [
        'order' => OrderService::class,
    ];

    /**
     * Handle the log request.
     *
     * @param Request $request
     * @return void
     */
    public function index(Request $request, ActivityLogCriteriaInterface $criteria)
    {
        $service = app()->getInstance()->make($this->types[$request->type]);
        $model = $service->getByHashedId($request->id);

        $logs = $criteria->include(['user.details'])->model($model)->get();

        return new ActivityCollection($logs);
    }

    /**
     * Store the activity log entry.
     *
     * @param Request $request
     * @return void
     */
    public function store(Request $request, ActivityLogFactoryInterface $factory)
    {
        $service = app()->getInstance()->make($this->types[$request->type]);

        $model = $service->getByHashedId($request->id);

        $factory->against($model)
            ->as($request->user())
            ->with($request->properties)
            ->action($request->action ?: 'default')
            ->log($request->log ?: 'system');
    }
}
