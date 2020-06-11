<?php

namespace GetCandy\Api\Http\Controllers\ActivityLog;

use GetCandy\Api\Core\ActivityLog\Interfaces\ActivityLogCriteriaInterface;
use GetCandy\Api\Core\ActivityLog\Interfaces\ActivityLogFactoryInterface;
use GetCandy\Api\Core\Orders\Services\OrderService;
use GetCandy\Api\Core\Products\Services\ProductService;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Resources\ActivityLog\ActivityCollection;
use Illuminate\Http\Request;

class ActivityLogController extends BaseController
{
    /**
     * The service mappings that can be instantiated.
     *
     * @var array
     */
    protected $types = [
        'order' => OrderService::class,
        'product' => ProductService::class,
    ];

    /**
     * Handle the log request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \GetCandy\Api\Core\ActivityLog\Interfaces\ActivityLogCriteriaInterface  $criteria
     * @return \GetCandy\Api\Http\Resources\ActivityLog\ActivityCollection
     */
    public function index(Request $request, ActivityLogCriteriaInterface $criteria)
    {
        if (empty($this->types[$request->type])) {
            return $this->errorWrongArgs();
        }
        $service = app()->getInstance()->make($this->types[$request->type]);
        $model = $service->getByHashedId($request->id, true);

        $logs = $criteria->include(['user.details'])->model($model)->get();

        return new ActivityCollection($logs);
    }

    /**
     * Store the activity log entry.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \GetCandy\Api\Core\ActivityLog\Interfaces\ActivityLogFactoryInterface  $factory
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
